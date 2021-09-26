<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illumnate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api', ['except'=>['login', 'create', 'anauthorized']]);
    }

    public function anauthorized() {
        return response()->json(['error'=>'Não autorizado'], 401);
    }

    public function login(Request $request) {
        $array = ['error' => ''];

        $email = $request->input('email');
        $password = $request->input('password');

        if ($email && $password) {
            $token = \JWTAuth::attempt([
                'email' => $email,
                'password' => $password
            ]);

            if (!$token) {
                //$this->anauthorized();
                $array['error'] = 'Email ou senha inválidos';
                return $array;
            }

            $array["token"] = $token;
            return $array;
        } 
        
        $array['error'] = 'Dados não enviados';
        return $array;
    }
    
    public function logout() {

    }

    public function refresh() {

    }

    public function create(Request $request) {
        // POST *api/user (name, email, password, birthdate)
        $array = ['error' => ''];

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $birthdate = $request->input('birthdate');

        if ($name && $email && $password && $birthdate) {
            // Validar data de nascimento
            if(strtotime($birthdate) === false) {
                $array['error'] = 'Data de nascimento inválida';
                return $array;
            }

            // Verificar a existência do email
            $emailExists = User::where('email', $email)->count();

            if($emailExists === 0) {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $newUser = new User();

                $newUser->name = $name;
                $newUser->email = $email;
                $newUser->password = $hash;
                $newUser->birthdate = $birthdate;
                $newUser->save();

                $token = auth()->attempt([
                    'email' => $email,
                    'password' => $password
                ]);

                if (!$token) {
                    $array['error'] = 'Ocorreu um erro!';
                    return $array;
                } 
                
                $array['token'] = $token;

            } else {
                $array['error'] = 'E-mail já cadastrado. Tente outro.';
                return $array;
            }

        } else {
            $array['error'] = 'Não enviou todos os campos.';
            return $array;
        }

        return $array;
    }
}
