<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SgaLoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ReceitaController;
use App\Http\Controllers\DespesaController;
use App\Http\Controllers\CartaoController;
use App\Http\Controllers\CompraCartaoController;
use App\Http\Controllers\FaturaController;
use App\Http\Controllers\MovimentacaoContaController;
use App\Http\Controllers\TransferenciaController;
use App\Http\Controllers\RecorrenciaController;
use App\Http\Controllers\RelatorioController;

Route::get('/', function () {
    return view('welcome');
});

Route::get(
    '/dashboard',
    [DashboardController::class, 'index']
)
->middleware('auth')
->name('dashboard');

Route::get(
    '/acesso-sga',
    [SgaLoginController::class, 'entrar']
)->name('acesso.sga');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get(
    '/contas',
    [ContaController::class, 'index']
    )->name('contas.index');

    Route::get(
        '/contas/nova',
        [ContaController::class, 'create']
    )->name('contas.create');

    Route::post(
        '/contas',
        [ContaController::class, 'store']
    )->name('contas.store');

    Route::get(
        '/contas/{conta}/editar',
        [ContaController::class, 'edit']
    )->name('contas.edit');

    Route::put(
        '/contas/{conta}',
        [ContaController::class, 'update']
    )->name('contas.update');

    Route::patch(
        '/contas/{conta}/status',
        [ContaController::class, 'alternarStatus']
    )->name('contas.status');

    Route::get(
    '/categorias',
    [CategoriaController::class, 'index']
    )->name('categorias.index');

    Route::get(
        '/categorias/nova',
        [CategoriaController::class, 'create']
    )->name('categorias.create');

    Route::post(
        '/categorias',
        [CategoriaController::class, 'store']
    )->name('categorias.store');

    Route::get(
        '/categorias/{categoria}/editar',
        [CategoriaController::class, 'edit']
    )->name('categorias.edit');

    Route::put(
        '/categorias/{categoria}',
        [CategoriaController::class, 'update']
        )->name('categorias.update');

        Route::patch(
            '/categorias/{categoria}/status',
            [CategoriaController::class, 'alternarStatus']
        )->name('categorias.status');

        Route::get(
        '/receitas',
        [ReceitaController::class, 'index']
    )->name('receitas.index');

    Route::get(
        '/receitas/nova',
        [ReceitaController::class, 'create']
    )->name('receitas.create');

    Route::post(
        '/receitas',
        [ReceitaController::class, 'store']
    )->name('receitas.store');

    Route::get(
        '/receitas/{receita}/editar',
        [ReceitaController::class, 'edit']
    )->name('receitas.edit');

    Route::put(
        '/receitas/{receita}',
        [ReceitaController::class, 'update']
    )->name('receitas.update');

    Route::post(
        '/receitas/{receita}/receber',
        [ReceitaController::class, 'receber']
    )->name('receitas.receber');

    Route::get(
        '/despesas',
        [DespesaController::class, 'index']
    )->name('despesas.index');

    Route::get(
        '/despesas/nova',
        [DespesaController::class, 'create']
    )->name('despesas.create');

    Route::post(
        '/despesas',
        [DespesaController::class, 'store']
    )->name('despesas.store');

    Route::get(
        '/despesas/{despesa}/editar',
        [DespesaController::class, 'edit']
    )->name('despesas.edit');

    Route::put(
        '/despesas/{despesa}',
        [DespesaController::class, 'update']
    )->name('despesas.update');

    Route::post(
        '/despesas/{despesa}/pagar',
        [DespesaController::class, 'pagar']
    )->name('despesas.pagar');

    Route::post(
        '/despesas/{despesa}/cancelar',
        [DespesaController::class, 'cancelar']
    )->name('despesas.cancelar');

    Route::get(
        '/cartoes',
        [CartaoController::class, 'index']
    )->name('cartoes.index');

    Route::get(
        '/cartoes/novo',
        [CartaoController::class, 'create']
    )->name('cartoes.create');

    Route::post(
        '/cartoes',
        [CartaoController::class, 'store']
    )->name('cartoes.store');

    Route::get(
        '/cartoes/{cartao}/editar',
        [CartaoController::class, 'edit']
    )->name('cartoes.edit');

    Route::put(
        '/cartoes/{cartao}',
        [CartaoController::class, 'update']
    )->name('cartoes.update');

    Route::patch(
        '/cartoes/{cartao}/status',
        [CartaoController::class, 'alternarStatus']
    )->name('cartoes.status');

    Route::get(
        '/compras-cartao',
        [CompraCartaoController::class, 'index']
    )->name('compras-cartao.index');

    Route::get(
        '/compras-cartao/nova',
        [CompraCartaoController::class, 'create']
    )->name('compras-cartao.create');

    Route::post(
        '/compras-cartao',
        [CompraCartaoController::class, 'store']
    )->name('compras-cartao.store');

    Route::get(
        '/faturas',
        [FaturaController::class, 'index']
    )->name('faturas.index');

    Route::post(
        '/faturas/{fatura}/pagar',
        [FaturaController::class, 'pagar']
    )->name('faturas.pagar');

        Route::get(
        '/movimentacoes',
        [
            MovimentacaoContaController::class,
            'index'
        ]
    )->name('movimentacoes.index');

    Route::get(
        '/transferencias',
        [TransferenciaController::class, 'index']
    )->name('transferencias.index');

    Route::get(
        '/transferencias/nova',
        [TransferenciaController::class, 'create']
    )->name('transferencias.create');

    Route::post(
        '/transferencias',
        [TransferenciaController::class, 'store']
    )->name('transferencias.store');

        Route::get(
        '/recorrencias',
        [RecorrenciaController::class, 'index']
    )->name('recorrencias.index');

    Route::get(
        '/recorrencias/nova',
        [RecorrenciaController::class, 'create']
    )->name('recorrencias.create');

    Route::post(
        '/recorrencias',
        [RecorrenciaController::class, 'store']
    )->name('recorrencias.store');

    Route::get(
        '/recorrencias/{recorrencia}/editar',
        [RecorrenciaController::class, 'edit']
    )->name('recorrencias.edit');

    Route::put(
        '/recorrencias/{recorrencia}',
        [RecorrenciaController::class, 'update']
    )->name('recorrencias.update');

    Route::patch(
        '/recorrencias/{recorrencia}/status',
        [RecorrenciaController::class, 'alternarStatus']
    )->name('recorrencias.status');

    Route::get(
    '/relatorios',
    [RelatorioController::class, 'index']
    )->name('relatorios.index');

});

require __DIR__.'/auth.php';
