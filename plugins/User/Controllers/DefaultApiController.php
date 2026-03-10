<?php

namespace Plugins\User\Controllers;

use App\Attributes\IsGranted;
use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Str;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/users/api")]
#[IsGranted("auth")]
#[IsGranted("role:admin")]
class DefaultApiController
{
    #[Route("/{id}")]
    public function getDetails($id)
    {
        $user = User::query()->with('addresses')->findOrFail($id);
        return response()->json([
            'success' => true,
            'user' => $user,
        ]);
    }

    #[Route("/{userId}/address/{addressId}/delete", methods: ["POST"])]
    public function deleteAddress($userId, $addressId)
    {
        $user = User::query()->find($userId);
        /** @var Address $address */
        $address = $user->addresses()->find($addressId);
        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => "Indirizzo non trovato"
            ]);
        }
        $address->delete();
        return response()->json([
            'success' => true,
        ]);
    }

    #[Route("/create", methods: ["POST"])]
    public function createUser()
    {
        $password = Str::random(8);
        //TODO: Send welcome email

        /** @var User $user */
        $user = User::query()->create([
            'firstname' => request("firstname"),
            'lastname' => request("lastname"),
            'email' => request("email"),
            "phone_number" => request("phone"),
            'password' => bcrypt($password),
        ]);

        $user->assignRole(request("groups"));
        return response()->json([
            'success' => true,
        ]);
    }
}
