<?php

namespace Plugins\Drive\Controllers;

use App\Attributes\IsGranted;
use Inertia\Inertia;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/drive")]
#[IsGranted("auth")]
#[IsGranted("role:admin")]
class DefaultController
{

    public function __construct()
    {

    }

    #[Route("/")]
    public function index()
    {
        return Inertia::render("Drive/Index", []);
    }
}
