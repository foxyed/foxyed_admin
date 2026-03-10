<?php

namespace Plugins\Users\Controllers;

use App\Attributes\IsGranted;
use App\Models\User;
use App\Utils\DataTable\DataTable;
use App\Utils\DataTable\Header;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/users/api')]
#[IsGranted('auth')]
#[IsGranted('role:admin')]
class UsersApiController
{
    #[Route('/list', methods: ['GET'])]
    public function list()
    {
        return DataTable::of(User::query())
            ->setHeaders([
                Header::make('firstname', 'Nome', 'center'),
                Header::make('lastname', 'Cognome', 'center'),
                Header::make('email', 'Email', 'center'),
                Header::make('phone_number', 'Telefono', 'center'),
                Header::make('active', 'Attivo', 'center', false),
                Header::make('groups', 'Gruppi', 'center', false),
            ])->make();
    }

    #[Route('/create', methods: ['POST'])]
    public function create()
    {
        $data = request()->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:255', 'unique:users,phone_number'],
            'password' => ['required', 'string', 'min:8'],
            'active' => ['sometimes', 'boolean'],
            'groups' => ['sometimes', 'array'],
            'groups.*' => ['string'],
        ]);

        $user = User::query()->create([
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'] ?? null,
            'password' => Hash::make($data['password']),
            'active' => $data['active'] ?? true,
        ]);

        if (!empty($data['groups'])) {
            $user->syncRoles($data['groups']);
        }

        return response()->json(['success' => true]);
    }

    #[Route('/{id}/update', methods: ['POST'])]
    public function update(int $id)
    {
        $user = User::query()->findOrFail($id);

        $data = request()->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone_number' => ['nullable', 'string', 'max:255', Rule::unique('users', 'phone_number')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'active' => ['sometimes', 'boolean'],
            'groups' => ['sometimes', 'array'],
            'groups.*' => ['string'],
        ]);

        $user->firstname = $data['firstname'];
        $user->lastname = $data['lastname'];
        $user->email = $data['email'];
        $user->phone_number = $data['phone_number'] ?? null;
        if (array_key_exists('active', $data)) {
            $user->active = $data['active'];
        }
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        if (array_key_exists('groups', $data)) {
            $user->syncRoles($data['groups'] ?? []);
        }

        return response()->json(['success' => true]);
    }

    #[Route('/{id}/delete', methods: ['POST'])]
    public function delete(int $id)
    {
        $user = User::query()->findOrFail($id);
        $user->delete();

        return response()->json(['success' => true]);
    }

    #[Route('/{id}/toggle-active', methods: ['POST'])]
    public function toggleActive(int $id)
    {
        $user = User::query()->findOrFail($id);
        $user->active = !$user->active;
        $user->save();

        return response()->json([
            'success' => true,
            'active' => $user->active,
        ]);
    }
}
