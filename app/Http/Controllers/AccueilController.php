<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// Raha hampiasa queryBuilder
use Illuminate\Support\Facades\DB;
use App\Models\Produit;
class AccueilController extends Controller
{
    public function index(){
      return view('PagePrincipale.accueil');  
    }

}
