<?php

namespace Plugins\Drive\Controllers;

use App\Attributes\IsGranted;
use Spatie\Dropbox\Client;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/drive/api")]
#[IsGranted("auth")]
class ApiController
{
    private Client $dropbox;

    public function __construct()
    {
        $this->dropbox = app('dropbox');
    }

    #[Route("/", "drive.list.files")]
    public function listDir()
    {
        $path = request()->query('dir', "");

        $entries = $this->dropbox->listFolder($path)['entries'];

        $parent = dirname($path);

        if ($parent === '.') {
            $parent = '';
        }

        return response()->json([
            'success' => true,
            'data' => $entries,
            'parent' => $parent,
            'current' => $path
        ]);
    }

    #[Route("/create-folder", 'drive.create.folder', methods: ["POST"])]
    public function createFolder()
    {
        $folderName = request("name");
        $path = request("path", "/");

        $folderPath = rtrim($path, '/') . '/' . ltrim($folderName, '/');

        $this->dropbox->createFolder($folderPath);
        return response()->json([
            'success' => true,
        ]);
    }

    #[Route("/delete-folder", 'drive.delete.folder', methods: ["POST"])]
    public function deleteFolder()
    {
        $folderName = request("name");
        $this->dropbox->delete($folderName);
        return response()->json([
            'success' => true,
        ]);
    }
}

