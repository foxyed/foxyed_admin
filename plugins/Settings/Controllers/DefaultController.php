<?php

namespace Plugins\Settings\Controllers;

use App\Attributes\IsGranted;
use App\Models\Settings;
use Inertia\Inertia;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/settings")]
#[IsGranted("role:admin")]
class DefaultController
{
    #[Route("/", methods: ["GET", "POST"])]
    public function index()
    {
        return Inertia::render('Settings/Index', []);
    }
}
