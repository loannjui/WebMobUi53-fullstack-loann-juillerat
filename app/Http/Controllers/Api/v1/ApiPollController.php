<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ApiPollController extends Controller
{
    /**
     * Display a listing of the authenticated user's polls.
     */
    public function index(Request $request)
    {
        // On charge les options de chaque sondage pour les afficher directement côté frontend
        $polls = $request->user()->polls()->with('options')->orderBy('created_at', 'desc')->get();

        return $polls;
    }

    // Retourne tous les sondages publiés (non brouillons) pour la page publique
    public function publicIndex()
    {
        $polls = Poll::where('is_draft', false)
            ->with(['user', 'options'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $polls;
    }

    /**
     * Display the specified poll by its secret token.
     */
    public function show(string $token)
    {
        $poll = Poll::with(['options' => function ($query) {
            $query->withCount('votes');
        }])->where('secret_token', $token)->first();

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        return $poll;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'question' => 'required|string',
            'is_draft' => 'boolean',
            'allow_multiple_choices' => 'boolean',
            'allow_vote_change' => 'boolean',
            'results_public' => 'boolean',
            'duration' => 'nullable|integer|min:0|max:30',
            'options' => 'array',
            'options.*' => 'string|max:255',
        ]);

        $poll = new Poll();
        $poll->title = $validated['title'] ?? null;
        $poll->question = $validated['question'];
        $poll->is_draft = $validated['is_draft'] ?? true;
        $poll->allow_multiple_choices = $validated['allow_multiple_choices'] ?? false;
        $poll->allow_vote_change = $validated['allow_vote_change'] ?? false;
        $poll->results_public = $validated['results_public'] ?? false;
        $poll->duration = isset($validated['duration']) ? $validated['duration'] * 86400 : null;
        $poll->user()->associate($request->user());

        $poll->save();

        // Si des options ont été envoyées, on les insère dans poll_options liées à ce sondage
        if (!empty($validated['options'])) {
            $poll->options()->createMany(
                array_map(fn($label) => ['label' => $label], $validated['options'])
            );
        }

        // On retourne le sondage avec ses options pour que le frontend puisse les afficher directement
        return response()->json($poll->load('options'), 201);
    }


    public function update(Request $request, string $id)
    {
        $poll = Poll::findOrFail($id);

        Gate::authorize('update', $poll);

        // Un sondage publié ne peut plus être modifié
        if (!$poll->is_draft) {
            return response()->json(['message' => 'Un sondage publié ne peut pas être modifié.'], 403);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'question' => 'required|string',
            'is_draft' => 'boolean',
            'allow_multiple_choices' => 'boolean',
            'allow_vote_change' => 'boolean',
            'results_public' => 'boolean',
            'duration' => 'nullable|integer|min:0|max:30',
            'options' => 'array',
            'options.*' => 'string|max:255',
        ]);

        $poll->title = $validated['title'] ?? null;
        $poll->question = $validated['question'];
        $poll->is_draft = array_key_exists('is_draft', $validated) ? $validated['is_draft'] : $poll->is_draft;
        $poll->allow_multiple_choices = $validated['allow_multiple_choices'] ?? $poll->allow_multiple_choices;
        $poll->allow_vote_change = $validated['allow_vote_change'] ?? $poll->allow_vote_change;
        $poll->results_public = $validated['results_public'] ?? $poll->results_public;
        $poll->duration = isset($validated['duration']) ? $validated['duration'] * 86400 : $poll->duration;

        $poll->save();

        // Si le champ options est présent dans la requête, on remplace toutes les options existantes
        if (array_key_exists('options', $validated)) {
            $poll->options()->delete(); // Supprime les anciennes options
            if (!empty($validated['options'])) {
                $poll->options()->createMany(
                    array_map(fn($label) => ['label' => $label], $validated['options'])
                );
            }
        }

        // On retourne le sondage mis à jour avec ses options
        return response()->json($poll->load('options'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $poll = Poll::findOrFail($id);

        Gate::authorize('delete', $poll);

        $poll->delete();

        return response()->noContent();
    }
}
