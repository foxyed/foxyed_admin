<?php

namespace Plugins\Books\Controllers;

use App\Attributes\IsGranted;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Course;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/books/api')]
#[IsGranted('auth')]
class BooksApiController
{
    #[Route('/course/{courseId}/book', methods: ['GET'])]
    public function getOrCreateBook(int $courseId)
    {
        $course = Course::query()->findOrFail($courseId);
        if (!Gate::allows('course.manage', $course)) {
            return response()->json(['success' => false, 'message' => 'Permessi insufficienti'], 403);
        }

        $book = Book::query()->firstOrCreate(
            ['course_id' => $course->id],
            ['title' => 'Libro - ' . $course->title, 'active' => true]
        );

        return response()->json([
            'success' => true,
            'book' => $book,
            'chapters' => $book->chapters()->get(),
        ]);
    }

    #[Route('/book/{bookId}/chapters/create', methods: ['POST'])]
    public function createChapter(int $bookId)
    {
        $book = Book::query()->findOrFail($bookId);
        $course = $book->course;
        if (!Gate::allows('course.manage', $course)) {
            return response()->json(['success' => false, 'message' => 'Permessi insufficienti'], 403);
        }

        $data = request()->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $maxOrder = (int) Chapter::query()->where('book_id', $book->id)->max('order');

        $ch = Chapter::query()->create([
            'book_id' => $book->id,
            'title' => $data['title'],
            'order' => $maxOrder + 1,
            'status' => 'draft',
        ]);

        return response()->json(['success' => true, 'chapter' => $ch]);
    }

    #[Route('/chapters/{id}', methods: ['GET'])]
    public function getChapter(int $id)
    {
        $ch = Chapter::query()->with('book.course')->findOrFail($id);
        if (!Gate::allows('course.manage', $ch->book->course)) {
            return response()->json(['success' => false, 'message' => 'Permessi insufficienti'], 403);
        }
        return response()->json(['success' => true, 'chapter' => $ch]);
    }

    #[Route('/chapters/{id}/update', methods: ['POST'])]
    public function updateChapter(int $id)
    {
        $ch = Chapter::query()->with('book.course')->findOrFail($id);
        if (!Gate::allows('course.manage', $ch->book->course)) {
            return response()->json(['success' => false, 'message' => 'Permessi insufficienti'], 403);
        }

        $data = request()->validate([
            'title' => ['required', 'string', 'max:255'],
            'content_json' => ['nullable'],
            'content_html' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ]);

        $ch->title = $data['title'];
        $ch->content_json = array_key_exists('content_json', $data) ? json_encode($data['content_json']) : $ch->content_json;
        $ch->content_html = $data['content_html'] ?? $ch->content_html;
        $ch->status = $data['status'] ?? $ch->status;
        $ch->save();

        return response()->json(['success' => true]);
    }

    #[Route('/chapters/{id}/delete', methods: ['POST'])]
    public function deleteChapter(int $id)
    {
        $ch = Chapter::query()->with('book.course')->findOrFail($id);
        if (!Gate::allows('course.manage', $ch->book->course)) {
            return response()->json(['success' => false, 'message' => 'Permessi insufficienti'], 403);
        }
        $ch->delete();
        return response()->json(['success' => true]);
    }

    #[Route('/book/{bookId}/chapters/reorder', methods: ['POST'])]
    public function reorderChapters(int $bookId)
    {
        $book = Book::query()->with('course')->findOrFail($bookId);
        if (!Gate::allows('course.manage', $book->course)) {
            return response()->json(['success' => false, 'message' => 'Permessi insufficienti'], 403);
        }

        $data = request()->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $ids = $data['ids'];
        $count = Chapter::query()->where('book_id', $book->id)->whereIn('id', $ids)->count();
        if ($count !== count($ids)) {
            return response()->json(['success' => false, 'message' => 'Lista capitoli non valida'], 422);
        }

        foreach ($ids as $i => $id) {
            Chapter::query()->where('book_id', $book->id)->where('id', $id)->update(['order' => $i + 1]);
        }

        return response()->json(['success' => true]);
    }
}
