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
    $fields = [
        'proyecto' => null,
        'titulo' => null,
        'descripcion' => null,
        'usuario' => null,
    ];

    foreach (preg_split('/\r?\n/', $request->input('content')) as $line) {
        [$key, $value] = array_map('trim', explode(':', $line, 2) + [null, null]);
        $key = strtolower($key);
        if (array_key_exists($key, $fields)) {
            $fields[$key] = $value;
        }
    }

    $project = Project::where('name', 'like', "%{$fields['proyecto']}%")->first();

    if (! $project) {
        return response()->json([
            'message' => 'Project not found',
        ], 404);
    }

    if (empty($fields['titulo'])) {
        return response()->json([
            'message' => 'Title is required',
        ], 422);
    }

    $task = (new CreateTask)->create($project, [
        'name' => $fields['titulo'],
        'description' => $fields['descripcion'] ?? '',
        'created_by_user_id' => User::first()->id,
        'assigned_to_user_id' => isset($fields['usuario']) ? User::where('name', 'like', "%{$fields['usuario']}%")->first()->id ?? null : null,
        'group_id' => $project->taskGroups()->orderBy('order_column')->first()->id,
        'pricing_type' => PricingType::HOURLY->value,
    ]);

    return response()->json([
        'message' => 'Task created successfully',
        'task' => $task->load(['assignedToUser:id,name', 'project:id,name', 'assignedToUser:id,name']),
    ]);
});
