<?php

namespace Plugins\Media\Controllers;

use App\Attributes\IsGranted;
use App\Models\Course;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/media/api')]
#[IsGranted('auth')]
class MediaApiController
{
    #[Route('/upload', methods: ['POST'])]
    public function upload()
    {
        /** @var UploadedFile|null $file */
        $file = request()->file('file');
        if (!$file) {
            return response()->json(['success' => false, 'message' => 'File mancante'], 422);
        }

        $courseId = request('course_id');
        $course = null;
        if ($courseId) {
            $course = Course::query()->findOrFail($courseId);
            if (!Gate::allows('course.manage', $course)) {
                return response()->json(['success' => false, 'message' => 'Permessi insufficienti'], 403);
            }
        }

        $path = $file->store('uploads', 'public');

        $media = Media::query()->create([
            'user_id' => request()->user()->id,
            'course_id' => $course?->id,
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        return response()->json([
            'success' => true,
            'id' => $media->id,
            'url' => $media->url(),
        ]);
    }
}
