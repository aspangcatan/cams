<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CentralAccess\AccessRightController;
use App\Http\Controllers\Api\CentralAccess\AccessSystemController;
use App\Http\Controllers\Api\CentralAccess\DeviceListController;
use App\Http\Controllers\Api\TdhUser\DesignationController;
use App\Http\Controllers\Api\TdhUser\DivisionController;
use App\Http\Controllers\Api\TdhUser\SectionController;
use App\Http\Controllers\Api\TdhUser\UserAccountController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('tdh-user')->group(function () {
    Route::apiResource('designations', DesignationController::class);
    Route::apiResource('divisions', DivisionController::class);
    Route::apiResource('sections', SectionController::class);
    Route::post('users/{user}/reset-password', [UserAccountController::class, 'resetPassword']);
    Route::apiResource('users', UserAccountController::class);
});

Route::prefix('central-access')->group(function () {
    Route::get('systems/{system}/assignments', [AccessSystemController::class, 'assignments']);
    Route::post('systems/{system}/assignments', [AccessSystemController::class, 'storeAssignments']);
    Route::put('systems/{system}/assignments/{assignment}', [AccessSystemController::class, 'updateAssignment']);
    Route::delete('systems/{system}/assignments/{assignment}', [AccessSystemController::class, 'destroyAssignment']);
    Route::apiResource('systems', AccessSystemController::class);
    Route::apiResource('access-rights', AccessRightController::class);
    Route::post('device-lists/{device_list}/approve', [DeviceListController::class, 'approve']);
    Route::apiResource('device-lists', DeviceListController::class);
});
