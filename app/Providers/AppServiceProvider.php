<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Support\PermissionAccess;
use App\Support\Permissions;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->bind('files', function () {
            return new Filesystem;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('id');

        Gate::before(function ($user, string $ability) {
            if (! $user || ! method_exists($user, 'getAuthIdentifier')) {
                return null;
            }

            // Only intercept known permission abilities to avoid breaking model policies.
            if (! Permissions::isKnown($ability)) {
                return null;
            }

            try {
                return PermissionAccess::userCan($user, $ability);
            } catch (\Throwable) {
                return false;
            }
        });

        RateLimiter::for('presensi', function (Request $request) {
            $key = $request->user()?->getAuthIdentifier() ?: $request->ip();
            return Limit::perMinute(12)->by('presensi:'.$key);
        });
    }
}
