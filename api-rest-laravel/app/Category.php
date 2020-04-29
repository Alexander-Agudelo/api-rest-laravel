<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //Declaro la tabla en concreto que va a utilizar
    protected $table = 'categories';
    
    //Relacion de uno a muchos
    //una categoria puede estar asignada a varios posts
    //me devuelve un array de objetos con todos los posts
    public function posts(){
        return $this->hasMany('App\Post');
    }
}
