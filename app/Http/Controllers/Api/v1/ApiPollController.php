<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollVote;
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

    // Retourne tous les sondages publiés avec le nombre de votes par option
    public function publicIndex(Request $request)
    {
        $query = Poll::where('is_draft', false)
            ->with(['user', 'options' => fn($q) => $q->withCount('votes')])
            ->orderBy('created_at', 'desc');

        if (!$request->user('sanctum')) {
            $query->where('results_public', true);
        }

        return $query->get();
    }

    /**
     * Display the specified poll by its secret token.
     */
    public function show(string $token, Request $request)
    {
        $poll = Poll::with(['user', 'options' => function ($query) {
            $query->withCount('votes');
        }])->where('secret_token', $token)->first();

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        $user = $request->user('sanctum');
        $poll->user_option_ids = $user
            ? $poll->votes()->where('user_id', $user->id)->pluck('poll_option_id')->toArray()
            : [];

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
            'duration' => 'required|integer|min:1|max:30',
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
        $durationDays = $validated['duration'] ?? 0;
        $poll->duration = $durationDays > 0 ? $durationDays * 86400 : null;
        $poll->user()->associate($request->user());

        if (!$poll->is_draft && $poll->duration) {
            $poll->started_at = now();
            $poll->ends_at = now()->addSeconds($poll->duration);
        }

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
            'duration' => 'required|integer|min:1|max:30',
            'options' => 'array',
            'options.*' => 'string|max:255',
        ]);

        $wasDraft = $poll->is_draft;

        $poll->title = $validated['title'] ?? null;
        $poll->question = $validated['question'];
        $poll->is_draft = array_key_exists('is_draft', $validated) ? $validated['is_draft'] : $poll->is_draft;
        $poll->allow_multiple_choices = $validated['allow_multiple_choices'] ?? $poll->allow_multiple_choices;
        $poll->allow_vote_change = $validated['allow_vote_change'] ?? $poll->allow_vote_change;
        $poll->results_public = $validated['results_public'] ?? $poll->results_public;

        if (array_key_exists('duration', $validated)) {
            $poll->duration = ($validated['duration'] > 0) ? $validated['duration'] * 86400 : null;
        }

        if ($wasDraft && !$poll->is_draft && $poll->duration) {
            $poll->started_at = now();
            $poll->ends_at = now()->addSeconds($poll->duration);
        }

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

    public function vote(Request $request, string $id)
    {
        $poll = Poll::findOrFail($id);

        if ($poll->is_draft) {
            return response()->json(['message' => 'Ce sondage n\'est pas disponible.'], 403);
        }

        if ($poll->ends_at && $poll->ends_at->isPast()) {
            return response()->json(['message' => 'La période de vote est terminée.'], 403);
        }

        $validated = $request->validate([
            'option_ids' => 'required|array|min:1',
            'option_ids.*' => 'integer|exists:poll_options,id',
        ]);

        if (!$poll->allow_multiple_choices && count($validated['option_ids']) > 1) {
            return response()->json(['message' => 'Ce sondage n\'autorise qu\'un seul choix.'], 422);
        }

        // Vérifie que toutes les options appartiennent bien à ce sondage
        $validOptionIds = $poll->options()->pluck('id')->toArray();
        foreach ($validated['option_ids'] as $optionId) {
            if (!in_array($optionId, $validOptionIds)) {
                return response()->json(['message' => 'Option invalide.'], 422);
            }
        }

        $user = $request->user();
        $hasVoted = $poll->votes()->where('user_id', $user->id)->exists();

        if ($hasVoted && !$poll->allow_vote_change) {
            return response()->json(['message' => 'Vous avez déjà voté pour ce sondage.'], 403);
        }

        if ($hasVoted) {
            $poll->votes()->where('user_id', $user->id)->delete();
        }

        foreach ($validated['option_ids'] as $optionId) {
            PollVote::create([
                'poll_id' => $poll->id,
                'user_id' => $user->id,
                'poll_option_id' => $optionId,
            ]);
        }

        return response()->json(['message' => 'Vote enregistré.', 'option_ids' => $validated['option_ids']]);
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
