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
            try {
                $start = Carbon::parse($request->start)->startOfDay();
            } catch (\Exception $e) {}
        }

        if ($request->filled('end') && !in_array($request->end, ['null', 'undefined'], true)) {
            try {
                $end = Carbon::parse($request->end)->endOfDay();
            } catch (\Exception $e) {}
        }

        return [$start, $end];
    }

 public function kpis(Request $request)
{
    if ($request->range === "custom") {
        [$dateStart, $dateEnd] = $this->getDateRange($request);
    } else {
        $range = $request->range ?? '30d';

        if ($range === 'today') {
            $dateStart = now()->startOfDay();
            $dateEnd = now();
        } elseif ($range === '7d') {
            $dateEnd = now();
            $dateStart = now()->subDays(7);
        } elseif ($range === '30d') {
            $dateEnd = now();
            $dateStart = now()->subDays(30);
        } elseif ($range === '24h') {
            $dateEnd = now();
            $dateStart = now()->subHours(24);
        } else {
            $dateEnd = now();
            $dateStart = now()->subDays(30);
        }
    }

    $periodDays = $dateStart->diffInDays($dateEnd) + 1;
    $prevEnd = (clone $dateStart)->subDay()->endOfDay();
    $prevStart = (clone $prevEnd)->subDays($periodDays - 1)->startOfDay();

    // === Chiffre d'affaires : basé uniquement sur les commandes livrées ===
    $totalRevenu = Commande::where('statut', 'livrée')
        ->whereBetween('dateCommande', [$dateStart, $dateEnd])
        ->sum('montantTotal');

    $prevRevenu = Commande::where('statut', 'livrée')
        ->whereBetween('dateCommande', [$prevStart, $prevEnd])
        ->sum('montantTotal');

    // Nombre de commandes : toutes les commandes créées dans la période
    $commandesCount = Commande::whereBetween('dateCommande', [$dateStart, $dateEnd])->count();
    $prevCommandesCount = Commande::whereBetween('dateCommande', [$prevStart, $prevEnd])->count();

    // Clients nouveaux
    $clientsNouveaux = Utilisateur::whereBetween('created_at', [$dateStart, $dateEnd])->count();
    $prevClientsNouveaux = Utilisateur::whereBetween('created_at', [$prevStart, $prevEnd])->count();

    // Nombre de produits vendus (lignes de commandes sur commandes livrées)
    $produitsVendus = DetailCommande::whereHas('commande', function ($q) use ($dateStart, $dateEnd) {
        $q->where('statut', 'livrée')
          ->whereBetween('dateCommande', [$dateStart, $dateEnd]);
    })->count();

    $prevProduitsVendus = DetailCommande::whereHas('commande', function ($q) use ($prevStart, $prevEnd) {
        $q->where('statut', 'livrée')
          ->whereBetween('dateCommande', [$prevStart, $prevEnd]);
    })->count();

    // Fonction pour calculer le % de variation
    $pctChange = function ($current, $previous) {
        if ($previous == 0) return $current == 0 ? 0 : 100;
        return round((($current - $previous) / $previous) * 100, 2);
    };

    return response()->json([
        'period' => [
            'start' => $dateStart->toDateTimeString(),
            'end'   => $dateEnd->toDateTimeString(),
        ],
        'kpis' => [
            'totalRevenu'              => $totalRevenu ?? 0,
            'totalRevenuChangePct'     => $pctChange($totalRevenu, $prevRevenu),
            'commandesCount'           => $commandesCount,
            'commandesCountChangePct'  => $pctChange($commandesCount, $prevCommandesCount),
            'clientsNouveaux'          => $clientsNouveaux,
            'clientsNouveauxChangePct' => $pctChange($clientsNouveaux, $prevClientsNouveaux),
            'produitsVendus'           => (int) $produitsVendus,
            'produitsVendusChangePct'  => $pctChange($produitsVendus, $prevProduitsVendus),
        ]
    ]);
}

    public function salesOverTime(Request $request)
{
    [$start, $end] = $this->getDateRange($request);

    $interval = $request->get('interval', 'day');

    if ($interval === 'day') {
        $rows = Commande::select(
                DB::raw('DATE(dateCommande) as period'),
                DB::raw('SUM(montantTotal) as total')
            )
            ->where('statut', 'livrée')
            ->whereBetween('dateCommande', [$start, $end])
            ->groupBy(DB::raw('DATE(dateCommande)'))
            ->orderBy('period')
            ->get();
    } elseif ($interval === 'week') {
        $rows = Commande::select(
                DB::raw("CONCAT(YEAR(dateCommande), '-W', LPAD(WEEK(dateCommande), 2, '0')) as period"),
                DB::raw('SUM(montantTotal) as total')
            )
            ->where('statut', 'livrée')
            ->whereBetween('dateCommande', [$start, $end])
            ->groupBy(DB::raw('YEAR(dateCommande)'), DB::raw('WEEK(dateCommande)'))
            ->orderBy('period')
            ->get();
    } else { // month
        $rows = Commande::select(
                DB::raw("DATE_FORMAT(dateCommande, '%Y-%m') as period"),
                DB::raw('SUM(montantTotal) as total')
            )
            ->where('statut', 'livrée')
            ->whereBetween('dateCommande', [$start, $end])
            ->groupBy(DB::raw("DATE_FORMAT(dateCommande, '%Y-%m')"))
            ->orderBy('period')
            ->get();
    }

    // Optionnel : formater les résultats pour le frontend
    $formatted = $rows->map(function ($row) {
        return [
            'period' => $row->period,
            'total'  => round((float) $row->total, 2),
        ];
    });

    return response()->json($formatted);
}

    /**
     * Ventilation par catégorie
     */
    public function salesByCategory(Request $request)
    {
        [$start, $end] = $this->getDateRange($request);

        $rows = DB::table('categories')
            ->leftJoin('produits', 'categories.numCategorie', '=', 'produits.numCategorie')
            ->leftJoin('detail_commandes', 'produits.numProduit', '=', 'detail_commandes.numProduit')
            ->leftJoin('commandes', function ($join) use ($start, $end) {
                $join->on('detail_commandes.numCommande', '=', 'commandes.numCommande')
                     ->where('commandes.statut', 'livrée')
                     ->whereBetween('commandes.dateCommande', [$start, $end]);
            })
            ->groupBy('categories.numCategorie', 'categories.nomCategorie')
            ->select(
                'categories.numCategorie',
                'categories.nomCategorie',
                DB::raw('COALESCE(SUM(detail_commandes.sousTotal), 0) as total')
            )
            ->orderBy('total', 'desc')
            ->get();

        return response()->json($rows);
    }

    public function topProducts(Request $request)
    {
        [$start, $end] = $this->getDateRange($request);
        $metric = $request->get('metric', 'ca');

        $rows = DetailCommande::join('produits', 'detail_commandes.numProduit', '=', 'produits.numProduit')
            ->join('commandes', 'detail_commandes.numCommande', '=', 'commandes.numCommande')
            ->join('utilisateurs', 'commandes.numUtilisateur', '=', 'utilisateurs.numUtilisateur')
            ->where('commandes.statut', 'livrée')
            ->whereBetween('commandes.dateCommande', [$start, $end])
            ->groupBy(
                'produits.numProduit',
                'produits.nomProduit',
                'utilisateurs.nomUtilisateur',
                'utilisateurs.image'
            )
            ->select(
                'produits.numProduit',
                'produits.nomProduit',
                'utilisateurs.nomUtilisateur as clientName',
                'utilisateurs.image as clientImage',
                DB::raw($metric === 'ca'
                    ? 'SUM(detail_commandes.sousTotal) as total'
                    : 'SUM(detail_commandes.poids) as total')
            )
            ->orderBy('total', 'desc')
            ->get();

        return response()->json($rows);
    }

public function topClients(Request $request)
{
    [$start, $end] = $this->getDateRange($request);
    $limit = $request->get('limit', 10);

    // Top clients : commandes non annulées
    $clients = Utilisateur::join('commandes', 'utilisateurs.numUtilisateur', '=', 'commandes.numUtilisateur')
        ->whereNotIn('commandes.statut', ['annulée'])
        ->whereBetween('commandes.dateCommande', [$start, $end])
        ->groupBy('utilisateurs.numUtilisateur', 'utilisateurs.nomUtilisateur', 'utilisateurs.image', 'utilisateurs.email')
        ->select(
            'utilisateurs.numUtilisateur',
            'utilisateurs.nomUtilisateur',
            'utilisateurs.image',
            'utilisateurs.email',
            DB::raw('COUNT(commandes.numCommande) as commandes_count'),
            DB::raw('SUM(commandes.montantTotal) as total_depense')
        )
        ->orderBy('commandes_count', 'desc')
        ->limit($limit)
        ->get();

    if ($clients->isEmpty()) {
        return response()->json([]);
    }

    $maxCommandes = $clients->max('commandes_count') ?: 1;

    $formatted = $clients->map(function ($client) use ($maxCommandes, $start, $end) {
        // Historique mensuel
        $historique = Commande::where('numUtilisateur', $client->numUtilisateur)
            ->whereNotIn('statut', ['annulée'])
            ->whereBetween('dateCommande', [$start, $end])
            ->selectRaw("DATE_FORMAT(dateCommande, '%Y-%m') as mois, COALESCE(SUM(montantTotal), 0) as total")
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

        // Produits achetés par ce client (poids = quantité)
        $produitsDetail = DetailCommande::join('produits', 'detail_commandes.numProduit', '=', 'produits.numProduit')
            ->join('commandes', 'detail_commandes.numCommande', '=', 'commandes.numCommande')
            ->where('commandes.numUtilisateur', $client->numUtilisateur)
            ->whereNotIn('commandes.statut', ['annulée'])
            ->whereBetween('commandes.dateCommande', [$start, $end])
            ->groupBy('produits.numProduit', 'produits.nomProduit')
            ->selectRaw('produits.nomProduit as nom, COALESCE(SUM(detail_commandes.poids), 0) as qte')
            ->orderBy('qte', 'desc')
            ->limit(6)
            ->get()
            ->map(fn($item) => [
                'nom' => $item->nom ?? 'Produit inconnu',
                'qte' => round((float)$item->qte, 2) // Arrondi à 2 décimales car c'est du poids
            ])
            ->filter(fn($p) => $p['qte'] > 0)
            ->values()
            ->toArray();

        if (empty($produitsDetail)) {
            $produitsDetail = [['nom' => 'Aucun produit', 'qte' => 1]];
            $produitsText = 'Aucun produit dans cette période';
        } else {
            $produitsText = collect($produitsDetail)
                ->map(fn($p) => $p['nom'] . ' (' . $p['qte'] . ' kg)')
                ->implode(', ');
        }

        $progress = round(($client->commandes_count / $maxCommandes) * 100, 2);

        return [
            'numUtilisateur'          => $client->numUtilisateur,
            'nomUtilisateur'          => $client->nomUtilisateur ?? 'Inconnu',
            'image'                   => $client->image,
            'email'                   => $client->email ?? 'Non renseigné',
            'commandes_count'         => (int) $client->commandes_count,
            'total_depense'           => round((float) $client->total_depense, 2),
            'progress_pct'            => $progress,
            'historique_achats'       => array_values($allMonths),
            'produits_preferes'       => $produitsText,
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

    public function getKpis(Request $request)
    {
        [$start, $end] = $this->getDateRange($request);

        $totalClients = Utilisateur::whereBetween('created_at', [$start, $end])->count();

        $clientsAvecPlusieursAchats = Utilisateur::whereHas('commandes', function ($q) use ($start, $end) {
            $q->where('statut', 'livrée')
              ->whereBetween('dateCommande', [$start, $end])
              ->select('numUtilisateur', DB::raw("COUNT(*) as total"))
              ->groupBy('numUtilisateur')
              ->havingRaw('COUNT(*) > 1');
        })->count();

        $repeatPurchaseRate = $totalClients > 0
            ? round(($clientsAvecPlusieursAchats / $totalClients) * 100, 2)
            : 0;

        $revenuTotal = Commande::where('statut', 'livrée')
            ->whereBetween('dateCommande', [$start, $end])
            ->sum('montantTotal');

        $totalCommandes = Commande::where('statut', 'livrée')
            ->whereBetween('dateCommande', [$start, $end])
            ->count();

        $annulations = Commande::where('statut', 'annulée')
            ->whereBetween('dateCommande', [$start, $end])
            ->count();

        $tauxRetour = $totalCommandes > 0
            ? round(($annulations / $totalCommandes) * 100, 2)
            : 0;

        return response()->json([
            'repeatPurchaseRate' => $repeatPurchaseRate,
            'revenuTotal' => $revenuTotal,
            'tauxRetour' => $tauxRetour,
        ]);
    }
}