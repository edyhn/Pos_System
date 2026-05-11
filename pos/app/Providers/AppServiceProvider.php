<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Blade::directive('currency', fn ($exp) => "<?php echo 'Rp ' . number_format($exp, 0, ',', '.'); ?>");

        Blade::directive('formatNumber', fn ($exp) => "<?php echo number_format($exp, 0, ',', '.'); ?>");
    }
}
