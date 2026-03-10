<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function manage(User $user, Course $course): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $course->teachers()->where('users.id', $user->id)->exists();
    }
}
