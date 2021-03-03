<?php

namespace App\Http\Livewire;

use App\Models\Game;
use App\Models\Slot;
use App\Models\User;
use Faker\Factory;
use Faker\Generator;
use Livewire\Component;

class VirtualGame extends Component
{
    public $game;
    public $bingo;
    public $currentNumber;
    public $winner;
    public $players;
    public $bingoNumbers;
    public $initNumbersArray;
    public $gameNumbersArray;
    public $iterations;
    public $winningNumbers;
    public $pointBoard;

    public function mount(Game $game) {
        $this->game = $game;
        $this->bingo = false;
        $this->currentSelection = 0;
        $this->winner = "";
        $this->bingoNumbers = [];
        $this->players = [];
        $this->initNumbersArray = range(1,75);
        $this->gameNumbersArray = range(1,75); // todo: shuffle
        $this->iterations = 0;
        $this->winningNumbers = "";
        $this->pointBoard = [];
//        shuffle($this->initNumbersArray);
//        shuffle($this->gameNumbersArray);

    }

    public function getListeners()
    {
        return [
//            "echo-private:update-slot.{$this->game->id},UpdateSlot" => 'reRenderSlots'
        ];
    }

    public function initGame() {
        $slots = Slot::where("game_id", $this->game->id)->where("confirmed",Slot::STATUS_CONFIRMED)->get();

        /** INIT PLAYERS
         * @var Slot $slot
         */
        foreach ($slots as $slot) {
            $email = $slot->user_email;
            $currentNumber = $slot->board_number;
            $owner = $email."_".$slot->board_number;
            $this->bingoNumbers[$slot->board_number] = $owner; // set by value
            $this->pointBoard[$owner]["points"] = 0;
            for($i=0; $i<4; $i++) {
                $currentNumber+=15;
                $this->bingoNumbers[$currentNumber] = $owner; // set by value
            }
        }


        /**
         * THE GAME ALGORITHM | RANDOM
         */
        while(!$this->bingo) {
            $randomKey = array_rand($this->gameNumbersArray,1);
            $this->currentSelection = $this->gameNumbersArray[$randomKey];
            unset($this->gameNumbersArray[$randomKey]);
            $owner = $this->bingoNumbers[$this->currentSelection];


            ++$this->pointBoard[$owner]["points"];
            $this->pointBoard[$owner]["numbers"][] = $this->currentSelection;
            if($this->pointBoard[$owner]["points"] == 5) {
                $this->bingo = true;
                $this->winner = User::where("email", explode("_", $owner)[0])->first()->name;
                sort($this->pointBoard[$owner]["numbers"]);
                $this->winningNumbers = implode(", ", $this->pointBoard[$owner]["numbers"]);
                return;
            }
        }

    }


    public function render()
    {
        $this->initGame();
        return view('livewire.virtual-game');
    }
}
