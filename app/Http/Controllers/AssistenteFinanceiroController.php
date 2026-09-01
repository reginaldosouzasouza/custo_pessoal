<?php

namespace App\Http\Controllers;

use App\Services\AssistenteFinanceiroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssistenteFinanceiroController extends Controller
{
    public function perguntar(
        Request $request,
        AssistenteFinanceiroService $assistente
    ): JsonResponse {
        $dados = $request->validate([
            'pergunta' => ['required', 'string', 'max:500'],
        ]);

        return response()->json([
            'resposta' => $assistente->responder(
                auth()->id(),
                $dados['pergunta']
            ),
        ]);
    }
}