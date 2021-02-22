<?php

namespace App\Jobs;

use App\Models\Game;
use App\Models\Slot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunGame implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $game;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        $slots = Slot::where("game_id", $this->game->id)->where("confirmed","!=",Game::STATUS_COMPLETED)->get();
//        $initNumbersArray = range(1,75);
//        $gameNumbersArray = range(1,75);
//        $winner = "";
//        $bingo = false;
//        $bingoNumbers = [];
//        $players = [];
//
//        while($initNumbersArray) {
//            foreach ($slots as $slot) {
//                $players[$slot->user_email] = 0;
//                $bingoNumbers[$slot->board_number] = $slot->user_email; // set by value
//                unset($initNumbersArray[$slot->board_number - 1]); // unset by key
//
//                for ($i=0; $i<4; $i++) {
//                    $randomKey = array_rand($initNumbersArray,1);
//                    $bingoNumbers[$initNumbersArray[$randomKey]] = $slot->user_email; // set by key
//                    unset($initNumbersArray[$randomKey]); // unset by key
//                }
//            }
//        }
//
//        while(!$bingo) {
//            $randomKey = array_rand($gameNumbersArray,1);
//            $currentNumber = $gameNumbersArray[$randomKey];
//            unset($gameNumbersArray[$randomKey]);
//            $email = $bingoNumbers[$currentNumber];
//            ++$players[$email];
//            if($players[$email] == 5) {
//                $bingo = true;
//                $winner = $email;
//            }
//        }

        // INSTEAD
        // each number is an index with an email like so
        /*
        [
            15 => ib@yopmail,
            11 => mr@yopmail,
            7  => br@yopmail,
        ]

        each time a number is selected, we give a point to an email. if that email has 5 points, BINGO!!!
        */



    }
}
