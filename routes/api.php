<?php

use Illuminate\Support\Facades\Route;

Route::resource('workflow', 'WorkflowController');
Route::resource('logs', 'LogController');
Route::resource('todos', 'TodoController')->only(['index']);
