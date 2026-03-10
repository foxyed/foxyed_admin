<?php

namespace Plugins\Drive\Controllers;

use App\Attributes\IsGranted;
use Inertia\Inertia;
use Spatie\Dropbox\Client;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/drive")]
#[IsGranted("auth")]
#[IsGranted("role:admin")]
class DefaultController
{
    private Client $dropbox;

    public function __construct()
    {
        $dropbox = app("dropbox");
        $this->dropbox = $dropbox;
    }

    #[Route("/")]
    public function index()
    {
        $folders = $this->dropbox->listFolder()['entries'];
        return Inertia::render("Drive/Index", [
            'folders' => $folders,
        ]);
    }
}
