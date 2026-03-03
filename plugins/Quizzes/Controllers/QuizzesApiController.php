<?php

namespace Plugins\Quizzes\Controllers;

use App\Attributes\IsGranted;
use App\Models\Chapter;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/quizzes/api')]
#[IsGranted('auth')]
class QuizzesApiController
{
    private function validateQuestionData(array $data): void
    {
        $type = $data['type'] ?? null;
        $payload = $data['data'] ?? null;

        if ($type === 'multiple_choice') {
            $options = is_array($payload) ? ($payload['options'] ?? null) : null;
            if (!is_array($options) || count($options) < 2) {
                abort(422, 'Scelta multipla: servono almeno 2 opzioni.');
            }
            $correct = array_filter($options, fn($o) => is_array($o) && !empty($o['correct']));
            if (count($correct) < 1) {
                abort(422, 'Scelta multipla: devi selezionare almeno 1 risposta corretta.');
            }
        }

        if ($type === 'true_false') {
            if (!is_array($payload) || !array_key_exists('correct', $payload)) {
                abort(422, 'Vero/Falso: manca il campo correct.');
            }
        }

        if ($type === 'fill_blank') {
            $answers = is_array($payload) ? ($payload['answers'] ?? null) : null;
            if (!is_array($answers) || count(array_filter($answers, fn($a) => trim((string)$a) !== '')) < 1) {
                abort(422, 'Riempi il vuoto: inserisci almeno 1 risposta valida.');
            }
        }
    }
    #[Route('/chapter/{chapterId}/quiz', methods: ['GET'])]
    public function getOrCreateQuiz(int $chapterId)
    {
        $chapter = Chapter::query()->with('book.course')->findOrFail($chapterId);
        if (!Gate::allows('course.manage', $chapter->book->course)) {
            return response()->json(['success' => false, 'message' => 'Permessi insufficienti'], 403);
        }

        $quiz = Quiz::query()->firstOrCreate(
            ['chapter_id' => $chapter->id],
            ['title' => 'Quiz - ' . $chapter->title, 'status' => 'draft']
        );

        return response()->json([
            'success' => true,
            'quiz' => $quiz,
            'questions' => $quiz->questions()->get(),
        ]);
    }

    #[Route('/quiz/{quizId}/update', methods: ['POST'])]
    public function updateQuiz(int $quizId)
    {
        $quiz = Quiz::query()->with('chapter.book.course')->findOrFail($quizId);
        if (!Gate::allows('course.manage', $quiz->chapter->book->course)) {
            return response()->json(['success' => false, 'message' => 'Permessi insufficienti'], 403);
        }

        $data = request()->validate([
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string'],
            'time_limit_seconds' => ['nullable', 'integer', 'min:1'],
            'attempt_limit' => ['nullable', 'integer', 'min:1'],
        ]);

        $quiz->fill($data);
        $quiz->save();

        return response()->json(['success' => true]);
    }

    #[Route('/quiz/{quizId}/questions/create', methods: ['POST'])]
    public function createQuestion(int $quizId)
    {
        $quiz = Quiz::query()->with('chapter.book.course')->findOrFail($quizId);
        if (!Gate::allows('course.manage', $quiz->chapter->book->course)) {
            return response()->json(['success' => false, 'message' => 'Permessi insufficienti'], 403);
        }

        $data = request()->validate([
            'type' => ['required', 'string'],
            'prompt' => ['required', 'string'],
            'points' => ['nullable', 'numeric', 'min:0'],
            'data' => ['nullable'],
            'explanation' => ['nullable', 'string'],
        ]);

        $this->validateQuestionData($data);

        $maxOrder = (int) QuizQuestion::query()->where('quiz_id', $quiz->id)->max('order');

        $q = QuizQuestion::query()->create([
            'quiz_id' => $quiz->id,
            'order' => $maxOrder + 1,
            'type' => $data['type'],
            'prompt' => $data['prompt'],
            'points' => $data['points'] ?? 1,
            'data' => $data['data'] ?? null,
            'explanation' => $data['explanation'] ?? null,
        ]);

        return response()->json(['success' => true, 'question' => $q]);
    }

    #[Route('/questions/{id}/update', methods: ['POST'])]
    public function updateQuestion(int $id)
    {
        $q = QuizQuestion::query()->with('quiz.chapter.book.course')->findOrFail($id);
        if (!Gate::allows('course.manage', $q->quiz->chapter->book->course)) {
            return response()->json(['success' => false, 'message' => 'Permessi insufficienti'], 403);
        }

        $data = request()->validate([
            'type' => ['required', 'string'],
            'prompt' => ['required', 'string'],
            'points' => ['nullable', 'numeric', 'min:0'],
            'data' => ['nullable'],
            'explanation' => ['nullable', 'string'],
        ]);

        $this->validateQuestionData($data);

        $q->type = $data['type'];
        $q->prompt = $data['prompt'];
        $q->points = $data['points'] ?? $q->points;
        $q->data = $data['data'] ?? null;
        $q->explanation = $data['explanation'] ?? null;
        $q->save();

        return response()->json(['success' => true]);
    }

    #[Route('/questions/{id}/delete', methods: ['POST'])]
    public function deleteQuestion(int $id)
    {
        $q = QuizQuestion::query()->with('quiz.chapter.book.course')->findOrFail($id);
        if (!Gate::allows('course.manage', $q->quiz->chapter->book->course)) {
            return response()->json(['success' => false, 'message' => 'Permessi insufficienti'], 403);
        }
        $q->delete();
        return response()->json(['success' => true]);
    }

    #[Route('/quiz/{quizId}/questions/reorder', methods: ['POST'])]
    public function reorderQuestions(int $quizId)
    {
        $quiz = Quiz::query()->with('chapter.book.course')->findOrFail($quizId);
        if (!Gate::allows('course.manage', $quiz->chapter->book->course)) {
            return response()->json(['success' => false, 'message' => 'Permessi insufficienti'], 403);
        }

        $data = request()->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $ids = $data['ids'];

        // ensure same set belongs to quiz
        $count = QuizQuestion::query()->where('quiz_id', $quiz->id)->whereIn('id', $ids)->count();
        if ($count !== count($ids)) {
            return response()->json(['success' => false, 'message' => 'Lista domande non valida'], 422);
        }

        foreach ($ids as $i => $id) {
            QuizQuestion::query()->where('quiz_id', $quiz->id)->where('id', $id)->update(['order' => $i + 1]);
        }

        return response()->json(['success' => true]);
    }
}
