<?php

namespace App\Providers;

use App\Events\InvoiceCreated;
use App\Events\InvoiceOverdue;
use App\Events\InvoiceSent;
use App\Events\PaymentRecorded;
use App\Listeners\LogActivity;
use App\Listeners\SendPaymentReceipt;
use App\Listeners\UpdateInvoiceStatus;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureEvents();
    }

    protected function configureEvents(): void
    {
        Event::listen(InvoiceCreated::class, [LogActivity::class, 'handle']);
        Event::listen(InvoiceSent::class, [LogActivity::class, 'handle']);
        Event::listen(InvoiceOverdue::class, [LogActivity::class, 'handle']);

        Event::listen(PaymentRecorded::class, [LogActivity::class, 'handle']);
        Event::listen(PaymentRecorded::class, [UpdateInvoiceStatus::class, 'handle']);
        Event::listen(PaymentRecorded::class, [SendPaymentReceipt::class, 'handle']);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
