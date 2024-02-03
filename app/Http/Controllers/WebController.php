<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use Carbon\Carbon;

use App\Models\Game;
use App\Models\Patch;

class WebController extends Controller
{
    public function getIndex(){

        return view('pages.home', [
            'games' => Game::where('title_id', 'like', 'CUSA%')->paginate(25),
            'total_games' => Game::count(),
            'total_patches' => Patch::count(),
            'day_patches' => Patch::whereBetween('created_at', [Carbon::now()->subDay(), Carbon::now()])->count(),
        ]);
    }

    public function getDetails($title_id) {
        $game = Game::with('patches')->where('title_id', $title_id)->first();

        if(!$game) {
            abort(404);
        }

        return view('pages.game', [
            'game' => $game,
        ]);
    }

    public function liveSearch(Request $request)
    {
        $query = $request->input('query');
    
        $games = Game::where('name', 'like', '%' . $query . '%')
                    ->orWhere('title_id', 'like', '%' . $query . '%')
                    ->get(['title_id', 'name']);
    
        return response()->json($games);
    }
}
