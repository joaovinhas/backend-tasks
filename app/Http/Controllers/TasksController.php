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
            'id_parent' => 'required|integer',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }else{

            $id_user = auth()->user()->id;

            if($request['id_parent'] == 0){

                $task = Tasks::create([
                    'task' => $request['new_task'],
                    'id_parent' => 0,
                    'id_user' => $id_user,
                    'concluded' => 0,
                    'timer' => null,
                ]);

                return response()->json(['success' => 'Task cadastrada com sucesso!']);

            }else{

                $id_parent = $request['id_parent'];

                $tasks = DB::select("select id from tasks where id_user = '$id_user' and id = '$id_parent'");

                if($tasks){

                    $task = Tasks::create([
                        'task' => $request['new_task'],
                        'id_parent' => $id_parent,
                        'id_user' => $id_user,
                        'concluded' => 0,
                        'timer' => null,
                    ]);

                    return response()->json(['success' => 'Task cadastrada com sucesso!']);
                }else{
                    return response()->json(['error' => 'Task alvo não encontrada!']);
                }
            }
        }
    }

    public function edit_task(Request $request){

        $validator = Validator::make($request->all(),[
            'id' => 'required|integer',
            'task' => 'required|string|max:255',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }else{

            $id_user = auth()->user()->id;
            $id_task = $request['id'];

            $tasks = DB::select("select id from tasks where id_user = '$id_user' and id = '$id_task'");

            if($tasks){

                $validatedData = (['task' => $request['task'] ]);

                Tasks::whereId($id_task)->update($validatedData);

                return response()->json(['success' => 'Task editada!']);

            }else{
                return response()->json(['error' => 'Task não encontrada!' ]);
            }
        }

    }

    public function all_tasks(){

        $id = auth()->user()->id;

        $root_tasks = DB::select("select * from tasks where id_parent = 0 AND id_user = '$id'");

        $child_tasks = DB::select("select * from tasks where id_parent != 0 AND id_user = '$id'");

        return response()->json(['root_tasks' => $root_tasks, 'child_tasks' => $child_tasks]);

    }

    public function search_task(Request $request){

        $id = auth()->user()->id;
        $status = auth()->user()->status;

        if($status != "block"){

            $validator = Validator::make($request->all(),[
                'search' => 'required|string',
                'type' => 'required|string',
            ]);

            if($validator->fails()){
                
                return response()->json($validator->errors()); 

            }else{

                $search = $request->search;

                switch ($request->type) {
                    case 'task':
                        $tasks = DB::select("select * from tasks where task like '%$search%' AND id_user = '$id'");
                        break;
                    case 'concluded':
                        if($search == 'concluded'){
                            $search = 1;
                        }else{
                            $search = 0;
                        }
                        $tasks = DB::select("select * from tasks where concluded like '%$search%' AND id_user = '$id'");
                        break;
                    default:
                        return response()->json(['error' => 'Valor Invalido!']);
                        die();
                        break;
                }

                return response()->json(['root_tasks' => $tasks]);

            }

        }

    }

    public function del_task(Request $request){

        $validator = Validator::make($request->all(),[
            'id' => 'required|integer',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }else{

            $id_user = auth()->user()->id;
            $id_task = $request['id'];

            $tasks = DB::select("select id from tasks where id_user = '$id_user' and id = '$id_task'");

            if($tasks){

                $this->del_tasks($id_task);

                $del_tasks = Tasks::findOrFail($id_task);
                $del_tasks->delete();

                return response()->json(['success' => 'Task apagada!' ]);
            }else{
                return response()->json(['error' => 'Task não encontrada!' ]);
            }
        }
    }

    public function del_tasks($id_task){

        $id_user = auth()->user()->id;

        $tasks = DB::select("select id from tasks where id_parent = '$id_task' and id_user = '$id_user' ");

        foreach($tasks as $task){
            $this->del_tasks($task->id);

            $del_tasks = Tasks::findOrFail($task->id);
            $del_tasks->delete();
        }
    }

}
