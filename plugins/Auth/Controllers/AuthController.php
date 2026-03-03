<?php

namespace Plugins\Auth\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/auth")]
class AuthController
{
    #[Route("/login", name: "login", methods: ["GET", "POST"])]
    public function login()
    {
        if (request()->isMethod("POST")) {
            if (Auth::attempt([
                'email' => request()->input('email'),
                'password' => request()->input('password'),
                'active' => true
            ])) {
                session()->regenerate();
                return redirect()->route("dashboard");
            }
        }
        return Inertia::render("Auth/Login", [

        ]);
    }

    #[Route("/logout", name: "logout")]
    public function logout()
    {
        Auth::logout();
        return Redirect::route("login");
    }
}
