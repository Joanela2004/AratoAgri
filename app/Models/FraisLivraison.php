<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FraisLivraison extends Model
{
    protected $primaryKey ='numFrais';
    protected $fillable=['poidsMin','poidsMax','frais'];
}
