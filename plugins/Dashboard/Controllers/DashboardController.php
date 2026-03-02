<?php

namespace Plugins\Dashboard\Controllers;

use App\Attributes\IsGranted;
use Inertia\Inertia;
use Symfony\Component\Routing\Attribute\Route;

#[IsGranted("auth")]
class DashboardController
{
    #[Route("/", name: 'dashboard')]
    public function index()
    {
        return Inertia::render("Dashboard/Index", []);
    }
}
