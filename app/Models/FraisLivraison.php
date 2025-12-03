<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FraisLivraison extends Model
{

    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $primaryKey ='numFrais';
    protected $fillable=['poidsMin','poidsMax','frais'];
}
