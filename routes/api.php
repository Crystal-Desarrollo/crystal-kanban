<?php

use App\Actions\Task\CreateTask;
use App\Enums\PricingType;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('tasks', function (Request $request) {
    $request->validate([
        'title' => 'required|string|min:5|max:50',
        'description' => 'required|string|min:5|max:255',
        'user' => 'nullable|string|min:3|max:20',
    ]);

    $project = Project::first();

    if (! $project) {
        return response()->json(['message' => 'No hay proyectos configurados.'], 500);
    }

    $task = (new CreateTask)->create($project, [
        'name' => $request->input('title'),
        'description' => $request->input('description'),
        'created_by_user_id' => User::first()->id,
        'assigned_to_user_id' => User::where('name', 'like', "%{$request->input('user')}%")->first()->id ?? null,
        'group_id' => $project->taskGroups()->orderBy('order_column')->first()->id,
        'pricing_type' => PricingType::HOURLY->value,
    ]);

    return response()->json([
        'message' => 'Task created successfully',
        'task' => $task->load(['assignedToUser:id,name', 'project:id,name']),
    ]);
});
