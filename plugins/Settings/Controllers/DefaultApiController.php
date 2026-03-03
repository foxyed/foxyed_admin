<?php

namespace Plugins\Settings\Controllers;

use App\Attributes\IsGranted;
use App\Models\Settings;
use App\Models\User;
use App\Utils\DataTable\DataTable;
use App\Utils\DataTable\Header;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/settings/api")]
#[IsGranted("auth")]
class DefaultApiController
{

    #[Route("/users/list")]
    #[IsGranted("role:admin")]
    public function getUsers()
    {
        return DataTable::of(User::query())
            ->setHeaders([
                Header::make("firstname","Nome","center"),
                Header::make("lastname","Cognome","center"),
                Header::make("email","Email","center"),
                Header::make("phone_number","Telefono","center"),
                Header::make("active","Attivo","center",false),
                Header::make("groups","Gruppi","center",false),
            ])->make();
    }

    #[Route("/dictionary/list")]
    #[IsGranted("role:admin")]
    public function getDictionary()
    {
        return DataTable::of(Settings::query())
            ->setHeaders([
                Header::make('label', 'Nome', 'center'),
                Header::make('key', 'Codice parametro', 'center', false),
                Header::make('value', 'Valore', 'center'),
                Header::make('is_live', 'In produzione', 'center', false),
            ])->make();
    }

    #[Route("/dictionary/create", methods: ["POST"])]
    public function setDictionaryItem()
    {
        $ok = Settings::query()->create([
            'key' => request('name'),
            'value' => request('value'),
            'is_live' => request('is_live', "off") === "on",
            'label' => request('label'),
        ]);
        if (!$ok) {
            return response()->json([
                'success' => false
            ]);
        }
        return response()->json([
            'success' => true
        ]);

    }

}
