<?php

namespace App\Http\Controllers;

use App\Events\UpdateGame;
use App\Events\UpdateSlot;
use App\Models\Game;
use App\Models\Slot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SlotController extends Controller
{

    public function test(Request $request) {
        $json = json_encode($request->all());

        $game = Game::where("id", 3)->first();
        $game->game_link = $json;
        $game->save();

    }
    public function roundTrip($product_id)
    {

        try {

            /** @var Game $game */
            $game = Game::where("product_id", $product_id)->where("status", "!=", Game::STATUS_COMPLETED)->first();
            if ($game->isGamePlayer()) {
                $slot = Slot::where("user_email", auth()->user()->email)->where("game_id", $game->id)->where("confirmed", 1)->first();
                if($slot) {
                    return redirect("/game/room/" . $game->id)->with("success", "Welcome back!");
                } else {
                    return redirect("/slot/next-steps/" . $game->id)->with("warning", "Did you take care of your numbers?");
                }

            } else {
                return redirect("/game")->with("error", "Sorry, you don't belong here.");
            }
        } catch (\Exception $e) {
            return redirect("/home")->with("error", "We're looking into this. thank you for your patience.");
        }
    }

    public function nextSteps(Game $game)
    {
        $slots = Slot::where("user_email", auth()->user()->email)->where("game_id", $game->id)->get();
        $link = $game->product_url . "?quantity=" . $slots->count() . "&ro"; // ro = readonly to discourage people from adjusting their quantity
        $numbers = "";
        foreach ($slots as $slot) {
            $numbers .= sprintf("%s | ", $slot->board_number);
        }
        return view("slots.next-steps",[
            'numbers' => $numbers,
            'link' => $link
        ]);
    }

    /**
     * API CALL
     */
    public function confirmSlot(Request $request, $product_id) {
        if(!Auth::check()) {
            abort(500);
        }

        /**
         * TODO: CHECK THE REFERRER, SHOULD BE THE STORE's URL, if not, request might be spoofed
         */
        $email = auth()->user()->email;
        $access = request('a');
        $totalPaid = request('t');

        if(!$email || !$product_id || !$access || !$totalPaid) {
            // most likely a spoofed request, log the issue
            dd($email, $product_id, $access, $totalPaid);
            return redirect("/home")->with("warning", "Please don't do that. We are watching");
        } elseif ($access != env('TOKEN_FROM_WP')) {
            // most likely a spoofed request, log the issue
            dd($access);
            return redirect("/home")->with("warning", "Please don't do that. We are watching");
        }

        $game = Game::where("product_id", $product_id)->where("status", "!=", Game::STATUS_COMPLETED)->first();

        if(!$game) {
            return redirect("/home")->with("warning", "This game doesn't exist or is closed. Please contact your vendor");
        }

        $slotsToConfirm = Slot::where("game_id", $game->id)->where("user_email", $email)->where("confirmed", Slot::STATUS_PENDING)->get();

        if(!$slotsToConfirm) {
            // most likely a spoofed request, log the issue
            return redirect("/home")->with("warning", "We dont have any slots from you playa...contact your vendor");
        }

        $totalToConfirm = $slotsToConfirm->count() * $game->buy_in;
        $unpaid = false;

        /**
         * if not paid all the way, only confirm what's
         * been paid already and have them pay the rest
         */
        if ($totalToConfirm > $totalPaid) {
            $quantityToBeFulfilled = ($totalToConfirm - $totalPaid) / $game->buy_in;
            $quantityAlreadyFulfilled = $totalPaid / $game->buy_in;
            $link = $game->product_url . "?quantity=" . $quantityToBeFulfilled . "&ro";
            $unpaid = true;
            $count = 0;
            foreach ($slotsToConfirm as $slot) {
                $slot->confirmed = Slot::STATUS_CONFIRMED;
                $slot->save();
                UpdateSlot::dispatch($game); // broadcasting to lobby
                ++$count;
                if($count == $quantityAlreadyFulfilled) {
                    break;
                }
            }
            return redirect("/slots/next-steps",[
                'link' => $link
            ])->with("warning", "Please take care of your numbers");
        }

        foreach ($slotsToConfirm as $slot) {
            $slot->confirmed = Slot::STATUS_CONFIRMED;
            $slot->save();
            UpdateSlot::dispatch($game); // broadcasting to lobby
        }

        $slots = Slot::where("game_id", $game->id)->where("confirmed", Slot::STATUS_CONFIRMED)->get()->count();

        /**
         * Not sure if we need to update the game status here
         */
        if($slots == 15) {
            $game->status = GAME::STATUS_VENDOR_TO_START;
            $game->save();
//            UpdateGame::dispatch($game); // broadcasting to lobby
        }

        return redirect("/game/room/".$game->id);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $game = Game::find($id);
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
        //
    }
}
