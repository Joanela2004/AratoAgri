<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Commande;
use App\Models\Paiement;
use App\Models\Produit;
use App\Models\DetailCommande;
use App\Models\Utilisateur;
use App\Models\Categorie;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * 🔵 Filtre de dates global utilisé par TOUTES les APIs
     */
    private function getDateRange(Request $request)
    {
        $start = now()->subDays(30)->startOfDay();
        $end   = now()->endOfDay();

        if ($request->filled('start') && !in_array($request->start, ['null','undefined'], true)) {
            try { $start = Carbon::parse($request->start)->startOfDay(); }
            catch (\Exception $e) {}
        }

        if ($request->filled('end') && !in_array($request->end, ['null','undefined'], true)) {
            try { $end = Carbon::parse($request->end)->endOfDay(); }
            catch (\Exception $e) {}
        }

        return [$start, $end];
    }

    /**
     * 🔵 KPIs évolutifs avec filtres personnalisés
     */
    public function kpis(Request $request)
    {
        // Si range = custom → on utilise getDateRange()
        if ($request->range === "custom") {
            [$dateStart, $dateEnd] = $this->getDateRange($request);
        } 
        else {
            // Sinon conserver ta logique
            $range = $request->range;

            if ($range === 'today') {
                $dateStart = now()->startOfDay();
                $dateEnd   = now();
            } elseif ($range === '7d') {
                $dateEnd   = now();
                $dateStart = now()->subDays(7);
            } elseif ($range === '30d') {
                $dateEnd   = now();
                $dateStart = now()->subDays(30);
            } elseif ($range === '24h') {
                $dateEnd   = now();
                $dateStart = now()->subHours(24);
            } else {
                $dateEnd   = now();
                $dateStart = now()->subDays(30);
            }
        }

        $periodDays = $dateStart->diffInDays($dateEnd) + 1;

        $prevEnd   = (clone $dateStart)->subDay()->endOfDay();
        $prevStart = (clone $prevEnd)->subDays($periodDays - 1)->startOfDay();

        $totalRevenu = Paiement::where('statut', 'effectué')
            ->whereBetween('datePaiement', [$dateStart, $dateEnd])
            ->sum('montantApayer');

        $prevRevenu = Paiement::where('statut', 'effectué')
            ->whereBetween('datePaiement', [$prevStart, $prevEnd])
            ->sum('montantApayer');

        $commandesCount = Commande::whereBetween('dateCommande', [$dateStart, $dateEnd])->count();
        $prevCommandesCount = Commande::whereBetween('dateCommande', [$prevStart, $prevEnd])->count();

        $clientsNouveaux = Utilisateur::whereBetween('created_at', [$dateStart, $dateEnd])->count();
        $prevClientsNouveaux = Utilisateur::whereBetween('created_at', [$prevStart, $prevEnd])->count();

        $produitsVendus = DetailCommande::whereHas('commande', function($q) use ($dateStart,$dateEnd){
            $q->whereBetween('dateCommande', [$dateStart, $dateEnd]);
        })
        ->select(DB::raw('SUM(poids) as totalPoids'), DB::raw('SUM(sousTotal) as totalCA'))
        ->first();

        $prevProduitsVendus = DetailCommande::whereHas('commande', function($q) use ($prevStart,$prevEnd){
            $q->whereBetween('dateCommande', [$prevStart, $prevEnd]);
        })
        ->select(DB::raw('SUM(poids) as totalPoids'), DB::raw('SUM(sousTotal) as totalCA'))
        ->first();

        $pctChange = function($current, $previous) {
            if ($previous == 0) return $current == 0 ? 0 : 100;
            return round((($current - $previous) / $previous) * 100, 2);
        };

        return response()->json([
            'period' => [
                'start' => $dateStart,
                'end'   => $dateEnd
            ],
            'kpis' => [
                'totalRevenu' => $totalRevenu ?? 0,
                'totalRevenuChangePct' => $pctChange($totalRevenu, $prevRevenu),
                'commandesCount' => $commandesCount,
                'commandesCountChangePct' => $pctChange($commandesCount, $prevCommandesCount),
                'clientsNouveaux' => $clientsNouveaux,
                'clientsNouveauxChangePct' => $pctChange($clientsNouveaux, $prevClientsNouveaux),
                'produitsVendusPoids' => (float)($produitsVendus->totalPoids ?? 0),
                'produitsVendusCA' => (float)($produitsVendus->totalCA ?? 0),
                'produitsVendusChangePct' => $pctChange($produitsVendus->totalCA ?? 0, $prevProduitsVendus->totalCA ?? 0),
            ]
        ]);
    }

    /**
     * 🔵 Courbe des ventes
     */
    public function salesOverTime(Request $request)
    {
        [$start, $end] = $this->getDateRange($request);
        $interval = $request->get('interval', 'day');

        if ($interval === 'day') {
            $rows = Commande::select(
                DB::raw('DATE(dateCommande) as period'),
                DB::raw('SUM(montantTotal) as total')
            )
            ->whereBetween('dateCommande', [$start, $end])
            ->groupBy(DB::raw('DATE(dateCommande)'))
            ->orderBy('period')
            ->get();
        } elseif ($interval === 'week') {
            $rows = Commande::select(
                DB::raw("CONCAT(YEAR(dateCommande), '-W', WEEK(dateCommande)) as period"),
                DB::raw('SUM(montantTotal) as total')
            )
            ->whereBetween('dateCommande', [$start, $end])
            ->groupBy(DB::raw("YEAR(dateCommande), WEEK(dateCommande)"))
            ->orderBy('period')
            ->get();
        } else {
            $rows = Commande::select(
                DB::raw("DATE_FORMAT(dateCommande, '%Y-%m') as period"),
                DB::raw('SUM(montantTotal) as total')
            )
            ->whereBetween('dateCommande', [$start, $end])
            ->groupBy(DB::raw("DATE_FORMAT(dateCommande, '%Y-%m')"))
            ->orderBy('period')
            ->get();
        }

        return response()->json($rows);
    }

    /**
     * 🔵 Ventilation par catégorie
     */
    public function salesByCategory(Request $request)
    {
        [$start, $end] = $this->getDateRange($request);

        $rows = DB::table('categories')
            ->leftJoin('produits', 'categories.numCategorie', '=', 'produits.numCategorie')
            ->leftJoin('detail_commandes', 'produits.numProduit', '=', 'detail_commandes.numProduit')
            ->leftJoin('commandes', function($join) use ($start, $end) {
                $join->on('detail_commandes.numCommande', '=', 'commandes.numCommande')
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

        $limit = intval($request->get('limit', 10));
        $metric = $request->get('metric', 'ca');

        $rows = DetailCommande::join('produits', 'detail_commandes.numProduit', '=', 'produits.numProduit')
            ->join('commandes', 'detail_commandes.numCommande', '=', 'commandes.numCommande')
            ->join('utilisateurs', 'commandes.numUtilisateur', '=', 'utilisateurs.numUtilisateur')
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
            ->limit($limit)
            ->get();

        return response()->json($rows);
    }

    public function topClients(Request $request)
    {
        [$start, $end] = $this->getDateRange($request);
        $limit = intval($request->get('limit', 10));

        $rows = Utilisateur::join('commandes', 'utilisateurs.numUtilisateur', '=', 'commandes.numUtilisateur')
            ->whereBetween('commandes.dateCommande', [$start, $end])
            ->groupBy('utilisateurs.numUtilisateur', 'utilisateurs.nomUtilisateur','utilisateurs.image')
            ->select(
                'utilisateurs.numUtilisateur',
                'utilisateurs.nomUtilisateur',
                'utilisateurs.image',
                DB::raw('COUNT(commandes.numCommande) as commandes_count'),
                DB::raw('SUM(commandes.montantTotal) as total_depense')
            )
            ->orderBy('total_depense', 'desc')
            ->limit($limit)
            ->get();

        return response()->json($rows);
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
    // 🔥 appliquer ton filtre global
    [$start, $end] = $this->getDateRange($request);

    // 🔵 Nombre total de clients créés dans la période
    $totalClients = Utilisateur::whereBetween('created_at', [$start, $end])->count();

    // 🔵 Clients avec plusieurs achats DANS la période
    $clientsAvecPlusieursAchats = Utilisateur::whereHas('commandes', function ($q) use ($start, $end) {
        $q->whereBetween('dateCommande', [$start, $end])
          ->select('numUtilisateur', DB::raw("COUNT(*) as total"))
          ->groupBy('numUtilisateur')
          ->havingRaw('COUNT(*) > 1');
    })->count();

    $repeatPurchaseRate = $totalClients > 0
        ? round(($clientsAvecPlusieursAchats / $totalClients) * 100, 2)
        : 0;

    // 🔵 Revenu dans la période
    $revenuTotal = Paiement::where('statut', 'effectué')
        ->whereBetween('datePaiement', [$start, $end])
        ->sum('montantApayer');

    // 🔵 Commandes dans la période
    $totalCommandes = Commande::whereBetween('dateCommande', [$start, $end])->count();

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
