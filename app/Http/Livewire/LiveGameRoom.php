<?php

namespace App\Http\Livewire;

use App\Models\Game;
use Livewire\Component;

class LiveGameRoom extends Component
{
    public $game;
    public $winner;
    public $link;
    public $isOwner;

    public function mount(Game $game) {
        $this->game = $game;
        $this->winner = $game->getGameWinner();
        $this->link = $game->game_link;
        $this->isOwner = $game->isGameOwner();
    }

    public function reRenderGame() {
        $this->game->refresh();
        $this->winner = $this->game->getGameWinner();
        $this->link = $this->game->game_link;
    }

    public function getListeners()
    {
        return [
//            "echo-private:update-game.{$this->game->id},UpdateGame" => 'reRenderGame'
        ];
    }

    public function render()
    {
        return view('livewire.live-game-room');
    }
}
