<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WithRole
{

    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next, $arg): Response
    {
        /** @var User $user */
        $user = $request->user();
        if (!$user) {
            session()->flash('notification', [
                'type' => 'error',
                'message' => "Utente non riconosciuto."
            ]);
            return redirect()->back();
        }
        if (!in_array($arg, $user->groups)) {
            session()->flash('notification', [
                'type' => 'error',
                'message' => "Permessi insufficienti."
            ]);
            return redirect()->back();
        }
        return $next($request);
    }
}
