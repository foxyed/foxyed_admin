<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;

class FilePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function view(User $user, File $file)
    {
        if (in_array('admin', $user->groups, true)) {
            return true;
        }

        if (in_array('teacher', $user->groups, true)) {
            return $file->owner_id === $user->id;
        }

        if (in_array('administrative', $user->groups, true)) {
            return in_array($file->type, ['contract', 'invoice'], true);
        }

        return false;
    }
}
