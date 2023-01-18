<?php

use App\Http\Controllers\Invest\SiteController;

use App\Http\Controllers\SegmentController;
use App\Http\Controllers\ToolsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('tilda/invest', [SiteController::class, 'invest_tilda']);

Route::post('tilda/apart', [SiteController::class, 'apart_tilda']);

Route::post('tilda/invest/webinar', [SiteController::class, 'webinar_tilda']);

Route::post('segment/hook', [SegmentController::class, 'success']);

Route::post('segment/create', [SegmentController::class, 'create']);

Route::post('segment/cron', [SegmentController::class, 'cron']);

Route::post('tools/company', [ToolsController::class, 'company']);
