<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class Setup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Role::findOrCreate("admin");
        Role::findOrCreate("student");
        Role::findOrCreate("teacher");
        Role::findOrCreate("administrative");

        $user = User::query()->where([
            'email' => 'rdisabatino97@gmail.com'
        ])->first();
        $user->assignRole("admin");
    }
}
