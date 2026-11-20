<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Livewire\Admin\PlatformSettings;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Dashboard\UserPanel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/cadastro', Register::class)->name('register');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    })->name('logout');

    Route::get('/painel', UserPanel::class)->name('dashboard');
    Route::get('/fornecedor/painel', UserPanel::class)->name('vendor.dashboard');

    Route::post('/painel/api-token', function (Request $request) {
        $request->user()->regenerateApiToken();

        return back()->with('status', 'Novo token de integração gerado com sucesso.');
    })->name('api-token.regenerate');

    Route::get('/admin/configuracoes', PlatformSettings::class)
        ->middleware(EnsureUserIsAdmin::class)
        ->name('admin.settings');
});
