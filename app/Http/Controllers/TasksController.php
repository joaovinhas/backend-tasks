<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Tasks;

class TasksController extends Controller
{   

    public function create_tasks(Request $request){

        echo("Criando tasks");

    }

    public function edit_task(Request $request){

        echo("Editando tasks");

    }

    public function all_tasks(){

        echo("Todas tasks");

    }

    public function search_task(Request $request){

        echo("Buscando tasks");

    }

    public function del_task(Request $request){

        echo("Apagando tasks");

    }
}
