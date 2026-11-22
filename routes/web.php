<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Livewire\Admin\PlatformSettings;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Dashboard\UserPanel;
use App\Livewire\Vendor\VendorSchedule;
use App\Livewire\Vendor\VendorShowcase;
use App\Livewire\Wedding\CoupleDashboard;
use App\Livewire\Wedding\PinterestBoard;
use App\Livewire\Wedding\PwaShareTarget;
use App\Livewire\Wedding\VendorWeddingAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/vitrine', VendorShowcase::class)->name('showcase.index');
Route::get('/vitrine/{slug}', VendorShowcase::class)->name('showcase.show');

Route::get('/acesso/profissional/{token}', VendorWeddingAccess::class)
    ->name('vendor.wedding.access');

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

    Route::get('/painel', CoupleDashboard::class)->name('dashboard');
    Route::get('/inspiracoes', PinterestBoard::class)->name('inspirations.index');
    Route::get('/compartilhar-pin', PwaShareTarget::class)->name('inspirations.pwa-share');
    Route::get('/fornecedor/painel', UserPanel::class)->name('vendor.dashboard');
    Route::get('/fornecedor/agenda', VendorSchedule::class)->name('vendor.schedule');

    Route::post('/painel/api-token', function (Request $request) {
        $request->user()->regenerateApiToken();

        return back()->with('status', 'Novo token de integração gerado com sucesso.');
    })->name('api-token.regenerate');

    Route::get('/admin/configuracoes', PlatformSettings::class)
        ->middleware(EnsureUserIsAdmin::class)
        ->name('admin.settings');
});
