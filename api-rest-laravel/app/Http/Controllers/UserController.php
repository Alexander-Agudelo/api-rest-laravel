<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class UserController extends Controller
{
    public function pruebas(Request $request){
        return "Acción de pruebas de USER-CONTROLLER";
    }

    //Accion de registro de usuarios:
    public function register(Request $request){

    	// Recoger los datos del usuario por post
    	$json = $request->input('json',null);

    	//Decodificar JSON
    	//Obtemgo un objeto
    	$params = json_decode($json);
    	// Si deseo un array
    	$params_array = json_decode($json, true);
    	//var_dump($params_array);
    	//var_dump($params); die();

    	if(!empty($params) && !empty($params_array)){ // valido que mis datos no vengas vacios
    		//limpiar datos 
	    	$params_array = array_map('trim', $params_array);

	    	// Validar datos
	    	$validate = \Validator::make($params_array, [
	    		'name'		=> 'required|alpha',
	    		'surname'	=> 'required|alpha',
	    		'email'		=> 'required|email|unique:users', // Comprobar si el usuario existe, duplicado
	    		'password'	=> 'required'
	    	]);

	    	if($validate->fails()){
	    		$data = array(
		    		'status'	=> 'error',
		    		'code'		=> 404,
		    		'message'	=> 'El usuario no se ha creado',
		    		'errors'	=> $validate->errors()
		    	);
	    		
	    	}else{ 
	    		//Validacion de usuario success

	    		// Cifrar la contrasena
	    		$pwd = hash('sha256', $params->password);
	    		var_dump($pwd);

		    	// Crear Usuario
		    	$user = new User();
		    	$user->name = $params_array['name'];
		    	$user->surname = $params_array['surname'];
		    	$user->email = $params_array['email'];
		    	$user->password = $pwd;
		    	$user->role ='ROLE_USER';

		    	// GUardar usuario
		    	$user->save();


	    		$data = array(
	    			'status'	=> 'success',
	    			'code'		=> 200,
	    			'message'	=> 'EL usuario se ha creado correctamente',
	    			'user'		=> $user
	    		);
	    	}
    	}else{
    		$data = array(
		    		'status'	=> 'error',
		    		'code'		=> 404,
		    		'message'	=> 'Los datos enviados no son correctos',
		    );
    	}
    	

    	return response()->json($data,$data['code']);
    }

    public function login(Request $request){

    	$jwtAuth = new \JwtAuth();

    	//Recibir datos por POST
    	$json = $request->input('json', null);
    	$params = json_decode($json);
    	$params_array = json_decode($json, true);

    	// Validar datos recibidos
    	$validate = \Validator::make($params_array, [
    					'email'		=> 'required|email',
    					'password'	=> 'required'
    	]);

    	if($validate->fails()){
    		// La validacion ha fallado
    		$signup = array(
    			'status'	=> 'error',
    			'code'		=>	404,
    			'message'	=> 'El usuario no se ha podido identificar',
    			'errors'	=> $validate->errors()
    		);
    	}else{

	    	//Cifrar password
			$pwd = hash('sha256', $params->password);

    		//Devolver token o datos
    		$signup = $jwtAuth->signup($params->email, $pwd);

    		//EN el caso que le pasemos ese parametro (gettoken), devolver 
    		//los datos decodificados en un json
    		if(!empty($params->gettoken)){
    			$signup = $jwtAuth->signup($params->email, $pwd, true);
    		}

    	}

    	return response()->json($signup, 200);

    }

    public function update(Request $request){
    	$token = $request->header("Authorization");
    	$jwtAuth = new \JwtAuth();
    	$checkToken = $jwtAuth-> checkToken($token);

    	if($checkToken){
    		echo "<h1>Login Correcto</h1>";
    	}else {
    		echo "<h1>Login Incorrecto</h1>";
    	}
    	die();
    }
}
