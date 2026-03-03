<?php

namespace Plugins\Courses\Controllers;

use App\Attributes\IsGranted;
use Inertia\Inertia;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/courses')]
#[IsGranted('role:admin')]
class CoursesController
{
    #[Route('/', methods: ['GET'])]
    public function index()
    {
        return Inertia::render('Courses/Index', []);
    }
}
