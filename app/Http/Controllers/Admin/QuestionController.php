<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionRequest;
use App\Models\Question;
use App\Orientation\Dimension;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(): View
    {
        $questions = Question::orderBy('ordre')->get();

        return view('admin.questions.index', [
            'questions' => $questions,
            'parDimension' => $questions->where('est_active', true)->countBy(
                fn (Question $question) => $question->dimension->value
            ),
            'dimensions' => Dimension::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.questions.form', [
            'question' => new Question(['est_active' => true, 'ordre' => (Question::max('ordre') ?? 0) + 1]),
            'dimensions' => Dimension::cases(),
        ]);
    }

    public function store(QuestionRequest $request): RedirectResponse
    {
        Question::create($request->validated());

        return redirect()
            ->route('admin.questions.index')
            ->with('succes', 'La question a été ajoutée.');
    }

    public function edit(Question $question): View
    {
        return view('admin.questions.form', [
            'question' => $question,
            'dimensions' => Dimension::cases(),
        ]);
    }

    public function update(QuestionRequest $request, Question $question): RedirectResponse
    {
        $question->update($request->validated());

        return redirect()
            ->route('admin.questions.index')
            ->with('succes', 'La question a été enregistrée.');
    }

    /**
     * Les résultats déjà calculés ne sont pas recalculés : ils gardent le profil
     * obtenu avec les questions de l'époque.
     */
    public function destroy(Question $question): RedirectResponse
    {
        $question->delete();

        return redirect()
            ->route('admin.questions.index')
            ->with('succes', 'La question a été supprimée.');
    }
}
