<?php

namespace App\Http\Controllers;

use App\Events\UpdateGame;
use App\Models\Game;
use App\Models\ProductList;
use App\Models\Slot;
use App\Notifications\GameHasStarted;
use App\Notifications\GameWinner;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class GameController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {

        return view('dashboard', [
            "games" => Game::where('user_id', auth()->id())->orderBy("id", "desc")->get()
        ]);
    }

    public function getUserGames() {
        $gameIds = DB::table("slots")->select("game_id")->where("user_email", auth()->user()->email)->distinct()->get();
        $idArray = [];
        if($gameIds) {
            foreach ($gameIds as $id) {
                $idArray[] = $id->game_id;
            }
        }

        $games = Game::whereIn("id", $idArray)->get();
        if(!$games->count()) {
            $games = Game::where("user_id", auth()->user()->id)->get();
        }


//        dd($games);

        return view('user.game-history', [
            "games" => $games
        ]);
    }

    public function gameRoom(Game $game) {
        if(!$game->isGameParticipant()) {
            session()->flash("error", "You are unauthorized to monitor this game. Please stop or you will be banned.");
            // log unauthorized activity
            return redirect("/home");
        }

        if($game->status == Game::STATUS_VENDOR_TO_START && $game->isGameOwner()) {
            return redirect("/game/lobby/{$game->id}")->with("info", "please start the game my guy");
        }

        return view("game.room", [
            "game" => $game,
            "isOwner" => $game->isGameOwner(),
            "video" => $game->game_link
        ]);
    }

    public function gameLobby(Game $game) {
        if($game->status == Game::STATUS_IN_PROGRESS) {
            return redirect("/game/room/{$game->id}");
        }

        if($game->getUser()->id == auth()->id()) {
            return view("game.lobby", [
                "game" => $game,
                "slots" => Slot::where("game_id", $game->id)->get()
            ]);
        }

        session()->flash("error", "You are unauthorized to monitor that game. Please stop or you will be banned.");
        return redirect()->back();

    }

    public function startGame(Request $request, Game $game) {
        if(!$game->isGameOwner()) {
            session()->flash("error", "This is not your game.");
            return redirect()->back();
        }

        if($game->status == Game::STATUS_VENDOR_TO_START) {
            $game->status = Game::STATUS_IN_PROGRESS;
            $game->game_link = $request->game_link;
            $game->save();
            UpdateGame::dispatch($game);
//            $this->notifyPlayersGameStarted($game); // iono yet
        } else {
            session()->flash("warning", "You cannot perform this action. Players have not all confirmed");
            return redirect()->back();
        }

        return view("game.room", [
            "game" => $game,
            "isOwner" => true
        ]);
    }

    public function completeGame(Request $request, Game $game) {
        if(!$game->isGameOwner()) {
            session()->flash("error", "This is not your game. Don't get banned outchea");
            return redirect()->back();
        }

        $request->validate([
            'winning_number' => 'string|required'
        ]);

        if($game->status == Game::STATUS_IN_PROGRESS) {
            $winner = Slot::where("game_id", $game->id)->where("board_number", $request->winning_number)->first();
            $game->winner = $winner->user_email;
            $game->status = Game::STATUS_COMPLETED;
            $game->save();
            UpdateGame::broadcast($game);
            Notification::send($game->getGameWinner(), new GameWinner($game));
        } else {
            session()->flash("warning", "You cannot perform this action. The game has not started");
            return redirect()->back();
        }

        session()->flash("success", "Say congrats to {$winner->user_email} or get yo ass knocked out");

        return redirect("/game");
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view("game.create", [
            "productList" => auth()->user()->productList,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function store(Request $request)
    {
        $product = DB::table('product_lists')->where("product_id", $request->product_id)->first();

        $gameExists = Game::where("product_id", $product->product_id)->where("status","<", Game::STATUS_COMPLETED)->first();
        if (empty($gameExists)) { // gotta get this

            $game = Game::create([
                'user_id' => auth()->id(),
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
                'product_url' => $product->product_url,
                'post_id' => $product->post_id,
                'product_image_url' => $product->product_image_url,
                'buy_in' => $product->buy_in,
                'prize' => $product->prize,
            ]);

            $game->refresh();

            $this->buildSlots($game);

            session()->flash('success', 'Game successfully created.');
        } else {
            session()->flash('error', 'Could not create new game. If you have a game of this type open already, please COMPLETE that one first');
        }

        return redirect("/game#game-".$game->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
//        $game = Game::find($id);
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // completed games
//        DB::table('slots')->where('game_id', $id)->delete();
//        Game::destroy($id);
    }

    private function buildSlots(Game $game)
    {
        for ($i=1; $i<=15; $i++) {
            try {
                Slot::create([
                    'game_id' => $game->id,
                    'game_product_id' => $game->product_id,
                    'user_email' => "",
                    'board_number' => $i,
                    'confirmed' => Slot::STATUS_NOT_CONFIRMED
                ]);
            } catch (\Exception $e) {
                continue;
                // log the error with the game ID and iteration
            }
        }
    }

    public function notifyPlayersGameStarted(Game $game) {
        $slots = $game->slots();
        $sent = [];
        foreach ($slots as $slot) {
            if($slot->user_email && !isset($sent[$slot->user_email])) {
                $sent[$slot->user_email] = true;
                Notification::send($slot->user(), new GameHasStarted($game));
            }
        }
    }
}
