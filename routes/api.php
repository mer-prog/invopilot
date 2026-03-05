<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ClientApiController;
use App\Http\Controllers\Api\InvoiceApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('clients', ClientApiController::class)->names('api.clients');
    Route::apiResource('invoices', InvoiceApiController::class)->names('api.invoices');
});
