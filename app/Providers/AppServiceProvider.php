<?php

namespace App\Providers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public const HOME = '/home';

    public function boot(): void
    {
        // Prevent "Specified key was too long" errors on MySQL with utf8mb4
        Schema::defaultStringLength(191);

        // Force HTTPS in production (Required for Render reverse proxy)
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Orders badge in the sidebar (today's completed checkouts)
        View::composer('layouts.master', function ($view) {
            $count = 0;

            if (Auth::check()) {
                $request = request();

                $query = Order::where('status', 'completed');

                // ដូច index(): បើ URL មាន date_from ឬ date_to → ប្រើតាមនោះ (អាចទុកចំហម្ខាង)
                // បើគ្មានទាំងពីរ → ថ្ងៃនេះ
                if ($request->has('date_from') || $request->has('date_to')) {
                    if ($request->filled('date_from')) {
                        $query->whereDate('order_date', '>=', $request->date_from);
                    }
                    if ($request->filled('date_to')) {
                        $query->whereDate('order_date', '<=', $request->date_to);
                    }
                } else {
                    $query->whereDate('order_date', now()->toDateString());
                }

                $count = $query
                    ->selectRaw('COUNT(DISTINCT COALESCE(pos_ref, order_number)) as c')
                    ->value('c') ?? 0;
            }

            $view->with('todayOrderCount', (int) $count);
        });

        $this->routes(function () {
            // API routes
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Web routes
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
