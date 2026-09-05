<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminFinanceiro
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $user = $request->user();

        if (
            !$user
            || !$user->ativo
            || !$user->is_admin
        ) {
            abort(403, 'Acesso não autorizado.');
        }

        return $next($request);
    }
}