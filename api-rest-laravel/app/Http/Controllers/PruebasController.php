<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//Cargamos estos paquetes para poder utilizar la clase y el modelo de cada uno
//ya puedo utilizar estos modelos para obtener datos de la base de datos
//o introducir datos, si quisiera trabajar con un usuario deberia llamarlo aca 
//para poder trabajr con el
use App\Post;
use App\Category;

class PruebasController extends Controller
{
    public function index(){
        $titulo = 'Animales';
        $animales = ['Perro', 'Gato', 'Tigre'];
        return view('pruebas.index', array(
            'titulo' => $titulo,
            'animales' => $animales
        ));
    }
    public function testOrm(){
        /*
        //obtiene un array de objetos con todos los datos de la tabla posts
        $posts = Post::all();
        foreach($posts as $post){
            echo "<h1>".$post->title."</h1>";
            echo "<span style='color:grey'>{$post->user->name} - {$post->category->name}</span>";
            echo "<p>".$post->content."</p>";
            echo '<hr>';
        }
         * 
         */
        
        
        //obtengo mis categorias y mis posts
        $categories = Category::all(); //obtengo todas mis categorias
        foreach($categories as $category){
            echo "<h1>{$category->name}</h1>";
            
            foreach($category->posts as $post){ //accedo a app\category.php y ya puedo acceder a mi metodo posts
                echo "<h3>".$post->title."</h3>";
                echo "<span style='color:grey'>{$post->user->name} - {$post->category->name}</span>";
                echo "<p>".$post->content."</p>";
                
            }
            echo '<hr>';
        }
        die();// NO me pide ninguna vista y cota la ejecucion del programa
    }
}

?>