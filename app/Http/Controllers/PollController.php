<?php

namespace App\Http\Controllers;

use App\Models\Poll;

class PollController extends Controller
{
    public function show(string $token)
    {
        return view('polls.show', ['token' => $token]);
    }

    public function index()
    {
        // Récupère tous les sondages publiés (non brouillons), avec auteur et options
        // Requête SQL inutilsée : le composant Vue dans polls/index.blade.php fetch les données via publicIndex
        $polls = Poll::where('is_draft', false)
            ->with(['user', 'options'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('polls.index', ['polls' => $polls]);
    }
}
