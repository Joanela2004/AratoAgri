<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $primaryKey='numArticle';
    protected $fillable=['titre','resume','description','image','auteur','datePublication'];
    
}
