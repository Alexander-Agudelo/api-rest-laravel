<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    //Declaro la tabla en concreto que va a utilizar
    protected $table = 'posts';

    protected $fillable = [
        'title', 'content', 'category_id',
    ];
    
    //Relacion de uno a mucho inversa(muchos a uno)
    //muchos posts pueden ser creados por un usuario 
    //o muchos post pueden pertenecer a una misma categoria
    
    //obtengo todo el objeto de usuario que ha creado ese post
    // con el user_id 
    public function user(){
        return $this->belongsTo('App\User', 'user_id');
    }
    
    //obtengo el objeto del modelo de categoria el cual  
    //esta relacionado con category_id
    public function category(){
        return $this->belongsTo('App\Category', 'category_id');
    }
               
}
