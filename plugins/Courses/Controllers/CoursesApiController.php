<?php

namespace Plugins\Courses\Controllers;

use App\Attributes\IsGranted;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\User;
use App\Utils\Courses\CourseCode;
use App\Utils\DataTable\DataTable;
use App\Utils\DataTable\Header;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/courses/api')]
#[IsGranted('auth')]
#[IsGranted('role:admin')]
class CoursesApiController
{
    // -------- Categories --------

    #[Route('/categories/list', methods: ['GET'])]
    public function listCategories()
    {
        return DataTable::of(CourseCategory::query())
            ->setHeaders([
                Header::make('name', 'Nome', 'center'),
                Header::make('slug', 'Slug', 'center'),
                Header::make('icon', 'Icon', 'center', false),
                Header::make('active', 'Attiva', 'center', false),
            ])->make();
    }

    #[Route('/categories/create', methods: ['POST'])]
    public function createCategory()
    {
        $data = request()->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:course_categories,slug'],
            'icon' => ['nullable', 'string', 'max:255'],
            'active' => ['sometimes', 'boolean'],
        ]);

        $slug = $data['slug'] ?? Str::slug($data['name']);

        CourseCategory::query()->create([
            'name' => $data['name'],
            'slug' => $slug,
            'icon' => $data['icon'] ?? null,
            'active' => $data['active'] ?? true,
        ]);

        return response()->json(['success' => true]);
    }

    #[Route('/categories/{id}/update', methods: ['POST'])]
    public function updateCategory(int $id)
    {
        $cat = CourseCategory::query()->findOrFail($id);

        $data = request()->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('course_categories', 'slug')->ignore($cat->id)],
            'icon' => ['nullable', 'string', 'max:255'],
            'active' => ['sometimes', 'boolean'],
        ]);

        $cat->name = $data['name'];
        $cat->slug = $data['slug'];
        $cat->icon = $data['icon'] ?? null;
        if (array_key_exists('active', $data)) {
            $cat->active = $data['active'];
        }
        $cat->save();

        return response()->json(['success' => true]);
    }

    #[Route('/categories/{id}/delete', methods: ['POST'])]
    public function deleteCategory(int $id)
    {
        CourseCategory::query()->findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    #[Route('/categories/{id}/toggle-active', methods: ['POST'])]
    public function toggleCategoryActive(int $id)
    {
        $cat = CourseCategory::query()->findOrFail($id);
        $cat->active = !$cat->active;
        $cat->save();
        return response()->json(['success' => true, 'active' => $cat->active]);
    }

    // -------- Courses --------

    #[Route('/list', methods: ['GET'])]
    public function listCourses()
    {
        $q = Course::query()
            ->select([
                'courses.*',
                'course_categories.name as category_name',
                'owners.firstname as owner_firstname',
                'owners.lastname as owner_lastname',
            ])
            ->join('course_categories', 'course_categories.id', '=', 'courses.course_category_id')
            ->join('course_user as cu', function ($join) {
                $join->on('cu.course_id', '=', 'courses.id')
                    ->where('cu.role', '=', 'owner');
            })
            ->join('users as owners', 'owners.id', '=', 'cu.user_id');

        return DataTable::of($q)
            ->setHeaders([
                Header::make('title', 'Titolo', 'center'),
                Header::make('code', 'Codice', 'center'),
                Header::make('category_name', 'Categoria', 'center', false),
                Header::make('owner_lastname', 'Owner', 'center', false),
                Header::make('price', 'Prezzo', 'center'),
                Header::make('active', 'Attivo', 'center', false),
            ])->make();
    }

    #[Route('/meta', methods: ['GET'])]
    public function meta()
    {
        $categories = CourseCategory::query()->where('active', true)->orderBy('name')->get(['id', 'name']);

        $teachers = User::query()
            ->where('active', true)
            ->whereHas('roles', fn($q) => $q->where('name', 'teacher'))
            ->orderBy('lastname')
            ->get(['id', 'firstname', 'lastname']);

        return response()->json([
            'categories' => $categories,
            'teachers' => $teachers,
        ]);
    }

    #[Route('/create', methods: ['POST'])]
    public function createCourse()
    {
        $data = request()->validate([
            'course_category_id' => ['required', 'integer', 'exists:course_categories,id'],
            'owner_user_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'active' => ['sometimes', 'boolean'],
        ]);

        $owner = User::query()->findOrFail($data['owner_user_id']);
        if (!$owner->active || !$owner->hasRole('teacher')) {
            return response()->json([
                'success' => false,
                'message' => 'Owner non valido (deve essere teacher attivo).'
            ], 422);
        }

        $category = CourseCategory::query()->findOrFail($data['course_category_id']);
        $code = CourseCode::nextForCategory($category);

        $course = Course::query()->create([
            'course_category_id' => $data['course_category_id'],
            'title' => $data['title'],
            'code' => $code,
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'active' => $data['active'] ?? true,
        ]);

        $course->teachers()->sync([
            $owner->id => ['role' => 'owner']
        ]);

        return response()->json(['success' => true]);
    }

    #[Route('/{id}/update', methods: ['POST'])]
    public function updateCourse(int $id)
    {
        $course = Course::query()->findOrFail($id);

        $data = request()->validate([
            'course_category_id' => ['required', 'integer', 'exists:course_categories,id'],
            'owner_user_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'active' => ['sometimes', 'boolean'],
        ]);

        $owner = User::query()->findOrFail($data['owner_user_id']);
        if (!$owner->active || !$owner->hasRole('teacher')) {
            return response()->json([
                'success' => false,
                'message' => 'Owner non valido (deve essere teacher attivo).'
            ], 422);
        }

        $course->course_category_id = $data['course_category_id'];
        $course->title = $data['title'];
        $course->description = $data['description'] ?? null;
        $course->price = $data['price'];
        if (array_key_exists('active', $data)) {
            $course->active = $data['active'];
        }
        $course->save();

        // Ensure single owner
        $course->teachers()->wherePivot('role', 'owner')->detach();
        $course->teachers()->syncWithoutDetaching([
            $owner->id => ['role' => 'owner']
        ]);

        return response()->json(['success' => true]);
    }

    #[Route('/{id}/delete', methods: ['POST'])]
    public function deleteCourse(int $id)
    {
        Course::query()->findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    #[Route('/{id}/toggle-active', methods: ['POST'])]
    public function toggleCourseActive(int $id)
    {
        $course = Course::query()->findOrFail($id);
        $course->active = !$course->active;
        $course->save();
        return response()->json(['success' => true, 'active' => $course->active]);
    }

    // -------- Teachers management (post-create) --------

    #[Route('/{id}/teachers/list', methods: ['GET'])]
    public function listTeachers(int $id)
    {
        $course = Course::query()->with('teachers')->findOrFail($id);

        return response()->json([
            'success' => true,
            'teachers' => $course->teachers->map(fn($t) => [
                'id' => $t->id,
                'firstname' => $t->firstname,
                'lastname' => $t->lastname,
                'role' => $t->pivot->role,
            ])->values(),
        ]);
    }

    #[Route('/{id}/teachers/add', methods: ['POST'])]
    public function addTeacher(int $id)
    {
        $course = Course::query()->findOrFail($id);

        $data = request()->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $teacher = User::query()->findOrFail($data['user_id']);
        if (!$teacher->active || !$teacher->hasRole('teacher')) {
            return response()->json([
                'success' => false,
                'message' => 'Docente non valido (deve essere teacher attivo).'
            ], 422);
        }

        $course->teachers()->syncWithoutDetaching([
            $teacher->id => ['role' => 'teacher']
        ]);

        return response()->json(['success' => true]);
    }

    #[Route('/{id}/teachers/remove', methods: ['POST'])]
    public function removeTeacher(int $id)
    {
        $course = Course::query()->findOrFail($id);

        $data = request()->validate([
            'user_id' => ['required', 'integer'],
        ]);

        // don't remove owner
        $isOwner = $course->teachers()
            ->where('users.id', $data['user_id'])
            ->wherePivot('role', 'owner')
            ->exists();

        if ($isOwner) {
            return response()->json([
                'success' => false,
                'message' => 'Non puoi rimuovere l\'owner.'
            ], 422);
        }

        $course->teachers()->detach($data['user_id']);
        return response()->json(['success' => true]);
    }
}
