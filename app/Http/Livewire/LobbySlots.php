<?php

namespace App\Http\Livewire;

use App\Models\Game;
use App\Models\Slot;
use Livewire\Component;

class LobbySlots extends Component
{
    public $slots;

    public $game;

    public function mount(Game $game)
    {
        $this->game = $game;
        $this->slots = Slot::where("game_id", $game->id)->get();
    }

    public function reRenderSlots() {
        $this->slots = Slot::where("game_id", $this->game->id)->get();

        if($this->allConfirmed()) {
            $this->game->status = Game::STATUS_VENDOR_TO_START;
            $this->game->save();
            $this->game->refresh();
        }
    }

    public function getListeners()
    {
        return [
            "echo-private:update-slot.{$this->game->id},UpdateSlot" => 'reRenderSlots'
        ];
    }

    public function render()
    {
        if($this->allConfirmed()) {
            $this->game->status = Game::STATUS_VENDOR_TO_START;
            $this->game->save();
            $this->game->refresh();
        }

        return view('livewire.lobby-slots');
    }

    public function allConfirmed()
    {
        return Slot::where("game_id", $this->game->id)->where("user_email","!=","")->where("confirmed", Slot::STATUS_CONFIRMED)->count() == 15;
    }
}
