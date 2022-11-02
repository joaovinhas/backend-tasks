<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TasksController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/register', [UserController::class, 'register'])->name('register');

Route::post('/login', [UserController::class, 'login'])->name('login');

//Rotas Protegidas
Route::group(['middleware' => ['auth:sanctum']], function () {

    //Users

    Route::get('/logout', [UserController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');

    Route::get('/show_user', [UserController::class, 'show_user'])->name('show_user');

    Route::post('/edit_user', [UserController::class, 'edit_user'])->name('edit_user');

    Route::post('/edit_permission', [UserController::class, 'edit_permission'])->name('edit_permission');

    Route::get('/show_users', [UserController::class, 'show_users'])->name('show_users');

    Route::post('/search_user', [UserController::class, 'search_user'])->name('search_user');

    Route::post('/del_user', [UserController::class, 'del_user'])->name('del_user');

    //Tasks

    Route::post('/create_task', [TasksController::class, 'create_task'])->name('create_task');

    Route::post('/edit_task', [TasksController::class, 'edit_task'])->name('edit_tasks');

    Route::get('/all_tasks', [TasksController::class, 'all_tasks'])->name('all_tasks');

    Route::post('/search_task', [TasksController::class, 'search_task'])->name('search_tasks');

    Route::post('/del_task', [TasksController::class, 'del_task'])->name('del_tasks');

});
