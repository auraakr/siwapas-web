<?php

namespace App\Providers;

use App\Http\Responses\AdminLogoutResponse;
use Carbon\CarbonImmutable;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\Translatable\Translatable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LogoutResponse::class, AdminLogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    /**
     * Applied here rather than in bootstrap/app.php so it is read from the (cached) config.
     */
    private function configureTrustedProxies(): void
    {
        $proxies = config('siwapas.trusted_proxies');

        if (! is_string($proxies) || trim($proxies) === '') {
            return;
        }

        TrustProxies::at(trim($proxies) === '*' ? '*' : array_map('trim', explode(',', $proxies)));
    }

    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureTrustedProxies();
        $this->configureRateLimiting();
        $this->configureTranslationFallback();
    }

    /**
     * Indonesian is the primary content language: when a translation is missing for the
     * active locale, show Indonesian, and then any other language that has been written.
     * The admin forms read raw translations, so this never fills the English fields.
     */
    protected function configureTranslationFallback(): void
    {
        $this->app->make(Translatable::class)->fallback(fallbackLocale: 'id', fallbackAny: true);
    }

    /**
     * Configure rate limiters for public write endpoints.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('public-write', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
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
