<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illumnate\Support\Facades\Auth;
use App\Models\User;

class UserController extends Controller
{
    private $loggedUser;

    public function __construct() {
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    public function update(Request $request) {
        $array = ['error' => ''];

        $name = $request->input('name');
        $email = $request->input('email');
        $birthdate = $request->input('birthdate');
        $city = $request->input('city');
        $work = $request->input('work');
        $password = $request->input('password');
        $password_confirm = $request->input('password_confirm');

        $model = new User();
        $user = $model->where('email', $this->loggedUser['email'])->first();

        //Name
        if ($name) {
            $this->name = $name;
        }

        //Email
        if ($email) {
            if($email != $user->email) {
                $emailExists = User::where('email', $email)->count();
                if ($emailExists === 0) {
                    $user->email = $email;
                } else {
                    $array['error'] = 'E-mail já existe';
                    return $array;
                }
            }
        }

        if ($birthdate) {
            if (strtotime($birthdate) === false) {
                $array['error'] = 'Data de nascimento inválida';
                return $array;
            }
            $user->birthdate = $birthdate;
        }

        if($city) {
            $user->city = $city;
        }

        if ($work) {
            $user->work = $work;
        }

        if ($password && $password_confirm) {
            if($password === $password_confirm) {
                $hash = password_hash($password, DEFAULT_PASSWORD);
                $user->password = $hash;
            } else {
                $array['error'] = 'As senhas não conferem.';
                return $array;
            }
        }

        $user->save();

        return $array;
    }
}
