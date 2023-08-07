<?php
namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\EntertainmentPlaceController;

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

/* AUTHENTICATION */
Route::group(['prefix' => 'auth'], function(){
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
    Route::get('/logged',    [AuthController::class, 'logged']);
});

Route::group(['prefix' => 'entertainment-place'], function(){
    Route::post('/store', [EntertainmentPlaceController::class, 'store']);
    Route::get('/{id}', [EntertainmentPlaceController::class, 'show']);
    Route::get('/{id}/profiles', [EntertainmentPlaceController::class, 'profiles']);
    Route::get('/{id}/edit', [EntertainmentPlaceController::class, 'edit']);
    Route::post('/{id}/update', [EntertainmentPlaceController::class, 'update']);
    Route::post('/{id}/personnel/create', [EntertainmentPlaceController::class, 'createPersonnel']);
});

/* ENTERTAINMENT PLACE PARTICIPATIONS */
Route::group(['prefix' => 'place-participations'], function(){
    Route::get('/signs/{date}', [PlaceParticipationsController::class, 'signedPerformersByMonth'])->name('api.place-participations.signed_performers');
    Route::get('/signs/{date}/edit', [PlaceParticipationsController::class, 'editParticipation'])->name('api.place-participations.signed_performer.edit');
    Route::put('/{id}/send-message', [PlaceParticipationsController::class, 'sendMessage'])->name('api.place-participations.send_message');
    Route::put('/{id}/send-notes', [PlaceParticipationsController::class, 'sendNote'])->name('api.place-participations.send_notes');
    Route::put('/{id}/send-honorarium', [PlaceParticipationsController::class, 'sendHonorarium'])->name('api.place-participations.send_honorarium');
    Route::post('/{id}/manage-invite', [PlaceParticipationsController::class, 'manageInvite'])->name('api.place-participations.manage_invite');

    Route::post('/update/{id}', [PlaceParticipationsController::class, 'update'])->name('api.place-participation.update');
    Route::put('/update-time/{id}', [PlaceParticipationsController::class, 'updateTime'])->name('api.place-participation.updateTime');


    Route::put('/remove/{id}', [PlaceParticipationsController::class, 'destroy'])->name('api.place-participation.remove');
    Route::post('/notes/{id}/create', [PlaceParticipationsController::class, 'addNote'])->name('api.place-participation.notes.create');

    /* CONVERSATIONS CONTROLLER */
    Route::group(['prefix' => 'conversation'], function(){
        Route::post('/{id}/store', [PlaceConversationsController::class, 'store'])->name('api.place.conversation.store');
        Route::put('/{id}/update-message', [PlaceConversationsController::class, 'updateMessage'])->name('api.place.conversation.update-message');
    });
});

/* ENTERTAINMENT PLACE PARTICIPATIONS */
Route::group(['prefix' => 'town-hall'], function(){
    Route::post('/store', [TownHallController::class, 'store']);
    Route::get('/{id}', [TownHallController::class, 'show']);
    Route::get('/{id}/profiles', [TownHallController::class, 'profiles']);
    Route::get('/{id}/edit', [TownHallController::class, 'edit']);
    Route::post('/{id}/update', [TownHallController::class, 'update']);
    Route::post('/{id}/personnel/create', [TownHallController::class, 'createPersonnel']);
});

/* PERFORMERS CONTROLLER */
Route::group(['prefix' => 'performer'], function(){
    Route::post('/store', [PerformerController::class, 'store']);
    Route::get('/{id}', [PerformerController::class, 'show']);
    Route::get('/{id}/profiles', [PerformerController::class, 'profiles']);
    Route::get('/{id}/edit', [PerformerController::class, 'edit']);
    Route::post('/{id}/update', [PerformerController::class, 'update']);
    Route::post('/{id}/personnel/create', [PerformerController::class, 'createPersonnel']);
});