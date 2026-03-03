<?php

namespace Plugins\Users\Controllers;

use App\Attributes\IsGranted;
use Inertia\Inertia;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/users')]
#[IsGranted('role:admin')]
class UsersController
{
    #[Route('/', methods: ['GET'])]
    public function index()
    {
        return Inertia::render('Users/Index', []);
    }
}
