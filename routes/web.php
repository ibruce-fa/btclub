<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductListController;
use App\Http\Controllers\SlotController;
use App\Http\Controllers\SocialController;
use App\Http\Livewire\LiveGameRoom;
use App\Http\Livewire\SlotReservations;
use App\Http\Livewire\VirtualGame;
use App\Models\Game;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminAuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if(auth()->check()) {
        return redirect("/home");
    }

    return view('welcome');
});

Route::get('/activeGames', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return redirect("/game");
});

Route::post("/random/test", function () {
    $json = json_encode(request()->all());

    $game = Game::where("id", 3)->first();
    $game->game_link = $json;
    $game->save();
});

Route::get('/home', [ HomeController::class, 'showActiveGames' ])->middleware("auth")->name("active-games");

//Route::get("/slot/test", [SlotController::class, "test"]);

Route::get("/slot/{game}", SlotReservations::class);
Route::get("/slot/next-steps/{game}", [SlotController::class, "nextSteps"]);


Route::resource("game", GameController::class)->names([
    'index' => 'dashboard',
    'store' => 'game.store'
])->middleware('admin');



Route::group(['middleware' => 'God'], function() {
    Route::resource("product-list", ProductListController::class);
});

Route::group(['middleware' => 'admin'], function() {
    Route::get("/game/lobby/{game}",[
        GameController::class, "gameLobby"
    ]);

    Route::post("/game/start/{game}",[
        GameController::class, "startGame"
    ]);

    Route::get("/game/start/{game}", function () {
        return redirect("/game");
    });

    Route::post("/game/complete/{game}",[
        GameController::class, "completeGame"
    ]);
});

Route::group(['middleware' => 'auth'], function() {
    Route::get('/home', [ HomeController::class, 'showActiveGames' ])->name("active-games");

    Route::get("/slot/{game}", SlotReservations::class);

    Route::get("/slot/confirm/{product_id}", [
        SlotController::class,'confirmSlot'
    ]);

    Route::get("/slot/next-steps/{game}", [
        SlotController::class, "nextSteps"
    ]);

    Route::get("/game/room/{game}",LiveGameRoom::class);

    Route::get("/game/user/history",[
        GameController::class, "getUserGames"
    ])->name("game-history");


    /**
     * in production, need middleware check if game has certain status and if current Auth'd user is game participant
     */
    Route::get("/game/room/virtual/{game}",VirtualGame::class);
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::get('auth/facebook', [SocialController::class, 'facebookRedirect']);

Route::get('auth/facebook/callback', [SocialController::class, 'loginWithFacebook']);
Route::get('privacy', [SocialController::class, 'privacy']);
Route::get('terms', [SocialController::class, 'termsOfService']);
Route::get('facebook-delete-data', [SocialController::class, 'deleteData']);
