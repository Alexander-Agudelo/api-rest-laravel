<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;

class UserController extends Controller
{
    public function pruebas(Request $request){
        return "Acción de pruebas de USER-CONTROLLER";
    }


    // Accion de registro de usuarios:
    public function register(Request $request){

    	// Recoger los datos del usuario por post
    	$json = $request->input('json',null);

    	// Decodificar JSON
    	// Obtemgo un objeto
    	$params = json_decode($json);
    	// Si deseo un array
    	$params_array = json_decode($json, true);
    	//var_dump($params_array);
    	//var_dump($params); die();

    	if(!empty($params) && !empty($params_array)){ // valido que mis datos no vengas vacios
    		// limpiar datos 
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
	    		// Validacion de usuario success

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

                // Genero un array de data con la informacion que acabo de guardar
                // envio el usuario en la rta
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
        // Llamo a mi servicio
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

    	//Comprobar si el usuario esta identificado
    	$token = $request->header("Authorization");
    	$jwtAuth = new \JwtAuth();
    	$checkToken = $jwtAuth-> checkToken($token);

		// Actualizar usuario
		// Recoger los datos por post
		$json = $request->input('json', null);
		$params_array = json_decode($json, true);


    	if($checkToken && !empty($params_array)){    		

    		//Obtener usuario identificado para usar su id en la siguiente peticion
    		$user = $jwtAuth->checkToken($token, true);

    		// Validar datos
    		$validate = \Validator::make($params_array, [
    			'name'		=> 'required|alpha',
	    		'surname'	=> 'required|alpha',
	    		'email'		=> 'required|email|unique:users,'.$user->sub // Comprobar si el usuario existe, duplicado
    		]);

    		// Quitar los campos que no quiero actualizar
    		unset($params_array['id']);
    		unset($params_array['role']);
    		unset($params_array['password']);
    		unset($params_array['created_at']);
    		unset($params_array['rember_token']);

    		// Actualiza usuario en bbdd
    		$user_update = User::where('id', $user->sub)->update($params_array);

    		// Devolver array con resultado
    		$data = array(
    			'code'		=> 200,
    			'status'	=> 'success',
    			'user'		=> $user,
    			'changes'	=> $params_array
    		);
			
    	}else {
    		$data = array(
    			'code'		=> 400,
    			'status'	=> 'error',
    			'message'	=> 'El usuario no esta identificado'
    		);
    	}
    	return response()->json($data, $data['code']);


    }



    public function upload(Request $request){

    	// Recoger datos de la peticion
    	$image = $request->file('file0');

        // Validacion de la imagen
        $validate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

    	// Guardar Imagen
    	if(!$image || $validate->fails()){
    		$data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'error al subir imagen imagen'
            );
    	}else{
			$image_name = time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));

            $data = array(
                'code'      => 200,
                'status'    => 'success',
                'image'     => $image_name
            );

    	}
    	
		return response()->json($data, $data['code']);


    }



    public function getImage($filename){
        $isset = \Storage::disk('users')->exists($filename);

        if($isset){
            $file = \Storage::disk('users')->get($filename);
            return new Response($file, 200);
        }else{
            $data = array(
                'code'      => 404,
                'status'    => 'error',
                'message'   => 'La imagen no existe'
            );
        }

        return response()->json($data, $data['code']);
       
    }

    public function detail($id){
        $user = User::find($id);

        if(is_object($user)){
            $data = array(
                'code'  => 200,
                'status'=> 'success',
                'user'  => $user
            );
        }else{
            $data = array(
                'code'      => 404,
                'status'    => 'error',
                'message'   => 'El usuario no existe'
            );
        }

        return response()->json($data, $data['code']);
        
    }
}
