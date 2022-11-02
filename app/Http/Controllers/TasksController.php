<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Tasks;
use Validator;

class TasksController extends Controller
{   

    public function create_task(Request $request){

        $validator = Validator::make($request->all(),[
            'new_task' => 'required|string|max:255',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }else{

            $id = auth()->user()->id;

            $task = Tasks::create([
                'task' => $request['new_task'],
                'id_parent' => 0,
                'id_user' => $id,
                'concluded' => 0,
                'timer' => null,
            ]);

            return response()->json(['success' => 'Task cadastrada com sucesso!']);
        }
    }

    public function edit_task(Request $request){

        echo("Editando tasks");

    }

    public function all_tasks(){

        $root_tasks = DB::select("select * from tasks where id_parent = 0");

        $child_tasks = DB::select("select * from tasks where id_parent != 0");

        return response()->json(['root_tasks' => $root_tasks, 'child_tasks' => $child_tasks]);

    }

    public function search_task(Request $request){

        echo("Buscando tasks");

    }

    public function del_task(Request $request){

        echo("Apagando tasks");

    }
}
