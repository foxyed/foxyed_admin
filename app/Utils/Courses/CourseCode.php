<?php

namespace App\Utils\Courses;

use App\Models\Course;
use App\Models\CourseCategory;

class CourseCode
{
    /**
     * Generate next course code based on category slug.
     * Format: E-<CAT>-NNN where CAT is derived from slug.
     * Example: slug "matematica" -> E-MAT-001
     */
    public static function nextForCategory(CourseCategory $category): string
    {
        $clean = preg_replace('/[^a-z]/i', '', $category->slug) ?: 'cat';
        $cat = strtoupper(substr($clean, 0, 3));
        $prefix = "E-{$cat}-";

        $last = Course::query()
            ->where('code', 'like', $prefix . '%')
            ->orderByDesc('code')
            ->value('code');

        $n = 0;
        if ($last && preg_match('/^(?:E-[A-Z]{3}-)(\d{3,})$/', $last, $m)) {
            $n = (int)$m[1];
        }

        $next = $n + 1;
        return $prefix . str_pad((string)$next, 3, '0', STR_PAD_LEFT);
    }
}
