<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Validator;

class UserController extends Controller
{
    public function login(Request $request){

        if (!Auth::attempt($request->only('email', 'password'))){
            return response()
                ->json(['error' => 'Unauthorized'], 401);
        }else{

            $user = User::where('email', $request['email'])->firstOrFail();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()
                ->json(['message' => 'Olá '.$user->name.', Bem Vindo a home','access_token' => $token, 'token_type' => 'Bearer', ]);
        }

    }

    public function register(Request $request){

        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }else{

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'status' => 'free',
                'permission' => 'cliente',
                'password' => Hash::make($request->password)
            ]);

            return response()->json(['success' => 'Usuario cadastrado com sucesso!']);
        }
    }

    public function show_user(){

        echo("Dados usuario");
    }

    public function edit_user(){

        echo("Editando usuario");
    }

    public function edit_permission(Request $request){

        echo("Editando usuario");
    }

    public function show_users(){

        echo("Todos usuarios");
    }

    public function search_user(Request $request){

        echo("Buscando usuarios");
    }

    public function del_user(Request $request){

        echo("Deletando usuario");
    }
}
