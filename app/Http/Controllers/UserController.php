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

        $id = auth()->user()->id;
        $permission = auth()->user()->permission;
        $status = auth()->user()->status;

        if($status != "block"){

            $user = ([ 'username' => auth()->user()->name , 'email' => auth()->user()->email]);

            return response()->json(['user' => $user]);

        }else{
            return response()->json(['error' => 'Usuario bloqueado!']);
        }
    }

    public function edit_user(Request $request){

        $id = auth()->user()->id;
        $status = auth()->user()->status;

        if($status != "block"){

            if(auth()->user()->name != $request['name'] AND auth()->user()->email != $request['email']){

                $validator = Validator::make($request->all(),[
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255|unique:users',
                ]);

                if($validator->fails()){
                    return response()->json($validator->errors());       
                }else{
                    $validatedData = ([ 'name' => $request['name'], 'email' => $request['email'] ]);
                }
                    
            
            }else if(auth()->user()->email != $request['email']){

                $validator = Validator::make($request->all(),[
                    'email' => 'required|string|email|max:255|unique:users',
                ]);

                if($validator->fails()){
                    return response()->json($validator->errors());       
                }else{
                     $validatedData = ([ 'email' => $request['email'] ]);
                }
            

            }else{

                $validator = Validator::make($request->all(),[
                    'name' => 'required|string|max:255',
                ]);

                if($validator->fails()){
                    return response()->json($validator->errors());       
                }else{
                    $validatedData = ([ 'name' => $request['name'] ]);
                }

            }

            User::whereId($id)->update($validatedData);

            return response()->json(['success' => 'Usuario editado com sucesso!']);



        }else{
            return response()->json(['error' => 'Usuario bloqueado!']);
        }

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

    public function edit_password(Request $request){

        $id = auth()->user()->id;
        $status = auth()->user()->status;

        if($status != "block"){

            $validator = Validator::make($request->all(),[
                'new_password' => 'required|string|min:8'
            ]);

            if($validator->fails()){
                return response()->json($validator->errors());       
            }else{

                $validatedData = ([ 'password' => Hash::make($request->new_password) ]);

                User::whereId($id)->update($validatedData);

                return response()->json(['success' => 'Senha alterada com sucesso!']);
            }
        }
    }

    public function show_users(){

        $id = auth()->user()->id;
        $permission = auth()->user()->permission;
        $status = auth()->user()->status;

        if($permission == "admin" AND $status != "block"){

            $users = DB::select("select name, email, permission, status from users where id != '$id'");

            return response()->json(['success' => $users]);

        }else{
            return response()->json(['error' => 'Usuario sem permissão']);
        }

    }

    public function search_user(Request $request){

        $id = auth()->user()->id;
        $permission = auth()->user()->permission;
        $status = auth()->user()->status;

        if($permission == "admin" AND $status != "block"){

            $validator = Validator::make($request->all(),[
                'search' => 'required|string',
                'type' => 'required|string',
            ]);

            if($validator->fails()){
                
                return response()->json($validator->errors()); 

            }else{

                $search = $request->search;

                switch ($request->type) {
                    case 'name':
                        $users = DB::select("select name, email, status, permission from users where name like '%$search%'");
                        break;
                    case 'email':
                        $users = DB::select("select name, email, status, permission from users where email like '%$search%'");
                        break;
                    case 'status':
                        $users = DB::select("select name, email, status, permission from users where status like '%$search%'");
                        break;
                    case 'permission':
                        $users = DB::select("select name, email, status, permission from users where permission like '%$search%'");
                        break;
                    default:
                        return response()->json(['error' => 'Valor Invalido!']);
                        die();
                        break;
                }

                return response()->json(['users' => $users]);

            }
        
        }
    }

    public function del_user(Request $request){

        echo("Deletando usuario");
    }
}
