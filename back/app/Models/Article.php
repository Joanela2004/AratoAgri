<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $primaryKey='numArticle';
    protected $fillable=['titre','contenu','description','image','auteur','datePublication'];
    
}
