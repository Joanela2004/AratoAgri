<?php
namespace Database\Seeders;
use App\Models\ModePaiement;

use Illuminate\Database\Seeder;
class ModePaiementSeeder extends Seeder
{
public function run()
{
    ModePaiement::updateOrCreate(
        ['nomModePaiement' => 'MVola / Orange Money'],
        [
            'actif' => true,
            'config' => ['numero' => '0343500000', 'nom' => 'RASOANIRINA Jean'],
        ]
    );

    ModePaiement::updateOrCreate(
        ['nomModePaiement' => 'Airtel Money'],
        ['actif' => true, 'config' => ['numero' => '0333500000']]
    );

    ModePaiement::updateOrCreate(
        ['nomModePaiement' => 'Espèces à la livraison'],
        ['actif' => true, 'config' => ['instructions' => 'Payez le livreur en cash']]
    );
}
}