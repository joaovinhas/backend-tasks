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

            $status = auth()->user()->status;

            if($status != "block"){

                $user = User::where('email', $request['email'])->firstOrFail();

                $token = $user->createToken('auth_token')->plainTextToken;

                return response()
                ->json(['username' => $user->name, 'access_token' => $token, 'token_type' => 'Bearer', ]);
            }else{
                return response()->json(['success' => 'Cliente esta bloqueado!']);
            }

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

    public function check_user(){

        $permission = auth()->user()->permission;
        $status = auth()->user()->status;

        $user = (['status' => $status, 'permission' => $permission]);

        return response()->json(['user' => $user]);

    }

    public function logout(){

        auth()->user()->tokens()->delete();

        return response()->json(['success' => 'Token deletado com sucesso!']);
    }

    public function dashboard(){

        $id = auth()->user()->id;
        $permission = auth()->user()->permission;
        $status = auth()->user()->status;

        if($status != "block"){

            $tasks = DB::select("select task from tasks where id_user = '$id'");

            if($permission == "admin"){

                $users = DB::select("select name from users");

                return response()->json(['users' => $users, 'tasks' => $tasks]);
            }else{
                return response()->json(['tasks' => $tasks]);
            }

        }else{
            return response()->json(['error' => 'Usuario bloqueado!']);
        }
    }

    public function show_user(){

        echo("show usuario");
    }

    public function edit_user(){

        return response()->json(['success' => 'Acesso ao edit user com sucesso!']);
    }

    public function edit_permission(Request $request){

        $id = auth()->user()->id;
        $permission = auth()->user()->permission;
        $status = auth()->user()->status;

        if($permission == "admin" AND $status != "block"){


            $validator = Validator::make($request->all(),[
                'id_user' => 'required|int',
                'permission' => 'required|string',
                'status' => 'required|string'
            ]);

            if($validator->fails()){
                
                return response()->json($validator->errors()); 

            }else{

                switch ($request->permission) {
                    case 'admin':
                        $permission_user = 'admin';
                        break;
                    case 'client':
                        $permission_user = 'client';
                        break;
                    default:
                        return response()->json(['error' => 'Valor Invalido!']);
                        die();
                        break;
                }

                switch ($request->status) {
                    case 'free':
                        $status_user = 'free';
                        break;
                    case 'unlimited':
                        $status_user = 'unlimited';
                        break;
                    case 'paid':
                        $status_user = 'paid';
                        break;
                    case 'block':
                        $status_user = 'block';
                        break;
                    default:
                        return response()->json(['error' => 'Valor Invalido!']);
                        die();
                        break;
                }

                $validatedData = (['status' => $status_user , 'permission' => $permission_user ]);

                $id_user = $request->id_user;

                User::whereId($id_user)->update($validatedData);

                return response()->json(['success' => 'Permissoes alteradas com sucesso!']);

            }
        }

    }

    public function show_users(){

        $id = auth()->user()->id;
        $permission = auth()->user()->permission;
        $status = auth()->user()->status;

        if($permission == "admin" AND $status != "block"){

            $users = DB::select("select id, name, email, permission, status from users");

            return response()->json(['success' => $users]);

        }else{
            return response()->json(['error' => 'Usuario sem permissão']);
        }

    }

    public function search_user(Request $request){

        echo("Buscando usuarios");
    }

    public function del_user(Request $request){

        echo("Deletando usuario");
    }
}
