<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Commande;
use App\Models\Paiement;
use App\Models\Produit;
use App\Models\DetailCommande;
use App\Models\Utilisateur;
use App\Models\Categorie;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private function getDateRange(Request $request)
    {
        $start = now()->subDays(30)->startOfDay();
        $end = now()->endOfDay();

        if ($request->filled('start') && !in_array($request->start, ['null', 'undefined'], true)) {
            $start = Carbon::parse($request->start)->startOfDay();
        }
        if ($request->filled('end') && !in_array($request->end, ['null', 'undefined'], true)) {
            $end = Carbon::parse($request->end)->endOfDay();
        }

        return [$start, $end];
    }

    public function kpis(Request $request)
    {
        [$dateStart, $dateEnd] = $this->getDateRange($request);
        $start = $dateStart->copy()->startOfDay();
        $end = $dateEnd->copy()->endOfDay();

        // === REVENU TOTAL GLOBAL (tous les temps) ===
        $revenuTotalGlobal = Paiement::where('statut', 'effectué')->sum('montantApayer');

        // === CALCULS POUR LA PÉRIODE SÉLECTIONNÉE ===
        $revenu = Paiement::where('statut', 'effectué')
            ->whereBetween('datePaiement', [$start, $end])
            ->sum('montantApayer');

        $totalPayees = Commande::where('statut', 'payée')
            ->whereBetween('dateCommande', [$start, $end])
            ->count();

        $commandesLivrees = Commande::where('statut', 'livrée')
            ->whereBetween('dateCommande', [$start, $end])
            ->count();

        // === NOMBRE D'ARTICLES VENDUS (quantité totale, pas le poids) ===
        $articlesVendus = DetailCommande::whereHas('commande', function ($q) use ($start, $end) {
            $q->where('statut', 'livrée')
              ->whereBetween('dateCommande', [$start, $end]);
        })->count(); // Chaque ligne = 1 article vendu

        $clientsNouveaux = Utilisateur::whereBetween('created_at', [$start, $end])->count();

        $annulations = Commande::where('statut', 'annulée')
            ->whereBetween('dateCommande', [$start, $end])
            ->count();

        $tauxAnnulation = $totalPayees > 0 ? round(($annulations / $totalPayees) * 100, 2) : 0;

        return response()->json([
            'period' => [
                'start' => $dateStart->toDateString(),
                'end'   => $dateEnd->toDateString(),
            ],
            'kpis' => [
                'revenu'              => round((float) $revenu, 2),
                'revenuTotalGlobal'   => round((float) $revenuTotalGlobal, 2),
                'totalCommandes'      => $commandesLivrees,
                'produitsVendus'      => (int) $articlesVendus, // ← CORRIGÉ : nombre d'articles
                'clientsNouveaux'     => $clientsNouveaux,
                'tauxAnnulation'      => $tauxAnnulation,
            ]
        ]);
    }

   public function salesOverTime(Request $request)
{
    [$start, $end] = $this->getDateRange($request);
    $interval = $request->get('interval', 'day');

    $rows = Commande::select(
        DB::raw("DATE(dateCommande) as period"),
        DB::raw("SUM(montantTotal) as total")
    )
        ->where('statut', 'livrée')
        ->whereBetween('dateCommande', [$start, $end])
        ->groupBy('period')
        ->orderBy('period')
        ->get()
        ->keyBy('period');

    // 🔥 Générer TOUTES les dates
    $dates = [];
    $current = $start->copy();

    while ($current <= $end) {
        $key = $current->format('Y-m-d');
        $dates[] = [
            'period' => $key,
            'total'  => isset($rows[$key]) ? (float) $rows[$key]->total : 0
        ];
        $current->addDay();
    }

    return response()->json($dates);
}

    private function getPeriodExpression($interval)
    {
        return match ($interval) {
            'day'   => 'DATE(dateCommande)',
            'week'  => "CONCAT(YEAR(dateCommande), '-W', LPAD(WEEK(dateCommande), 2, '0'))",
            'month' => "DATE_FORMAT(dateCommande, '%Y-%m')",
            default => 'DATE(dateCommande)',
        };
    }

   public function salesByCategory(Request $request)
{
    [$start, $end] = $this->getDateRange($request);

    $rows = DB::table('commandes')
        ->join('detail_commandes', 'commandes.numCommande', '=', 'detail_commandes.numCommande')
        ->join('produits', 'detail_commandes.numProduit', '=', 'produits.numProduit')
        ->join('categories', 'produits.numCategorie', '=', 'categories.numCategorie')
        ->where('commandes.statut', 'livrée')
        ->whereBetween('commandes.dateCommande', [$start, $end])
        ->groupBy('categories.numCategorie', 'categories.nomCategorie')
        ->select(
            'categories.numCategorie',
            'categories.nomCategorie',
            DB::raw('SUM(detail_commandes.sousTotal) as total')
        )
        ->orderByDesc('total')
        ->get();

    return response()->json($rows);
}


    public function topProducts(Request $request)
    {
        [$start, $end] = $this->getDateRange($request);
        $metric = $request->get('metric', 'ca'); // 'ca' ou 'quantity'

        $query = DetailCommande::join('produits', 'detail_commandes.numProduit', '=', 'produits.numProduit')
            ->join('commandes', 'detail_commandes.numCommande', '=', 'commandes.numCommande')
            ->where('commandes.statut', 'livrée')
            ->whereBetween('commandes.dateCommande', [$start, $end]);

        if ($metric === 'quantity') {
            // Quantité en nombre d'articles (count) ou poids selon besoin
            $query->select(
                'produits.numProduit',
                'produits.nomProduit',
                DB::raw('COUNT(detail_commandes.numDetail) as total') // Nombre d'articles
            )->groupBy('produits.numProduit', 'produits.nomProduit');
        } else {
            $query->select(
                'produits.numProduit',
                'produits.nomProduit',
                DB::raw('SUM(detail_commandes.sousTotal) as total')
            )->groupBy('produits.numProduit', 'produits.nomProduit');
        }

        $rows = $query->orderByDesc('total')->limit(10)->get();

        return response()->json($rows->map(fn($row) => [
            'numProduit' => $row->numProduit,
            'nomProduit' => $row->nomProduit,
            'total'      => round($row->total, 2),
            'unite'      => $metric === 'quantity' ? 'unités' : 'Ar'
        ]));
    }

   public function topClients(Request $request)
{
    [$start, $end] = $this->getDateRange($request);
    $limit = $request->get('limit', 10);

    // Partir des paiements effectués, puis remonter à l'utilisateur via la commande
    $clients = Utilisateur::join('commandes', 'utilisateurs.numUtilisateur', '=', 'commandes.numUtilisateur')
        ->join('paiements', 'commandes.numCommande', '=', 'paiements.numCommande')
        ->where('paiements.statut', 'effectué')
        ->whereBetween('paiements.datePaiement', [$start, $end])
        ->groupBy(
            'utilisateurs.numUtilisateur',
            'utilisateurs.nomUtilisateur',
            'utilisateurs.image',
            'utilisateurs.email'
        )
        ->select(
            'utilisateurs.numUtilisateur',
            'utilisateurs.nomUtilisateur',
            'utilisateurs.image',
            'utilisateurs.email',
            DB::raw('COUNT(DISTINCT commandes.numCommande) as commandes_count'),           // nb de commandes payées
            DB::raw('COUNT(DISTINCT paiements.numPaiement) as paiements_count'),           // nb de transactions
            DB::raw('SUM(paiements.montantApayer) as total_depense')                       // total réellement payé
        )
        ->orderByDesc('total_depense')
        ->limit($limit)
        ->get();

    if ($clients->isEmpty()) {
        return response()->json([]);
    }

    $maxDepense = $clients->max('total_depense') ?: 1;

    $formatted = $clients->map(function ($client) use ($start, $end, $maxDepense) {
        // Historique mensuel des dépenses (basé sur paiements effectués)
        $historique = Paiement::join('commandes', 'paiements.numCommande', '=', 'commandes.numCommande')
            ->where('commandes.numUtilisateur', $client->numUtilisateur)
            ->where('paiements.statut', 'effectué')
            ->whereBetween('paiements.datePaiement', [$start, $end])
            ->selectRaw("DATE_FORMAT(paiements.datePaiement, '%Y-%m') as mois, COALESCE(SUM(paiements.montantApayer), 0) as total")
            ->groupBy('mois')
            ->orderBy('mois')
            ->pluck('total', 'mois')
            ->toArray();

        $allMonths = [];
        $current = clone $start->startOfMonth();
        while ($current <= $end->startOfMonth()) {
            $key = $current->format('Y-m');
            $allMonths[$key] = $historique[$key] ?? 0;
            $current->addMonth();
        }

        // Produits préférés (basés sur les commandes payées effectivement)
        $produitsDetail = DetailCommande::join('commandes', 'detail_commandes.numCommande', '=', 'commandes.numCommande')
            ->join('paiements', 'commandes.numCommande', '=', 'paiements.numCommande')
            ->join('produits', 'detail_commandes.numProduit', '=', 'produits.numProduit')
            ->where('commandes.numUtilisateur', $client->numUtilisateur)
            ->where('paiements.statut', 'effectué')
            ->whereBetween('paiements.datePaiement', [$start, $end])
            ->groupBy('produits.numProduit', 'produits.nomProduit')
            ->selectRaw('produits.nomProduit as nom, COUNT(*) as qte') // nombre d'unités vendues
            ->orderByDesc('qte')
            ->limit(6)
            ->get()
            ->map(fn($item) => [
                'nom' => $item->nom ?? 'Produit inconnu',
                'qte' => (int) $item->qte
            ])
            ->filter(fn($p) => $p['qte'] > 0)
            ->values()
            ->toArray();

        $produitsText = empty($produitsDetail)
            ? 'Aucun produit dans cette période'
            : collect($produitsDetail)->map(fn($p) => $p['nom'] . ' (' . $p['qte'] . ' unités)')->implode(', ');

        $progress = round(($client->total_depense / $maxDepense) * 100, 2);

        return [
            'numUtilisateur'         => $client->numUtilisateur,
            'nomUtilisateur'         => $client->nomUtilisateur ?? 'Inconnu',
            'image'                  => $client->image,
            'email'                  => $client->email ?? 'Non renseigné',
            'commandes_count'        => (int) $client->commandes_count,
            'paiements_count'        => (int) $client->paiements_count,
            'total_depense'          => round((float) $client->total_depense, 2),
            'progress_pct'           => $progress,
            'historique_achats'      => array_values($allMonths),
            'produits_preferes'      => $produitsText,
            'produits_preferes_detail' => $produitsDetail,
        ];
    });

    return response()->json($formatted);
}

   public function stockAlerts(Request $request)
    {
        [$start, $end] = $this->getDateRange($request);
        $threshold = $request->get('threshold', 1.0);

        $produits = Produit::where('poids', '<=', $threshold)
            ->orderBy('poids', 'asc')
            ->get();

        return response()->json($produits);
    }
}