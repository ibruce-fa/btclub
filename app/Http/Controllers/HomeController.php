<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function showActiveGames() {
        if(auth()->user()->is_admin == 1) {
            return redirect("/game");
        }
        return view("user.home");
    }
}
