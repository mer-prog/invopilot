<?php

use App\Http\Controllers\Settings\ApiTokenController;
use App\Http\Controllers\Settings\OrganizationController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('user-password.edit');

    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/appearance')->name('appearance.edit');

    Route::get('settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])
        ->name('two-factor.show');

    Route::get('settings/organization', [OrganizationController::class, 'edit'])->name('organization.edit');
    Route::patch('settings/organization', [OrganizationController::class, 'update'])->name('organization.update');

    Route::get('settings/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
    Route::post('settings/api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
    Route::delete('settings/api-tokens/{token}', [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');
});
