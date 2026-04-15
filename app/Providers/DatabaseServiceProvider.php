<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Optimize SQLite for desktop application
        if (config('database.default') === 'sqlite' || config('database.default') === 'sqlite:memory') {
            $this->optimizeSQLite();
        }
    }

    /**
     * Optimize SQLite connection for desktop usage.
     */
    protected function optimizeSQLite(): void
    {
        try {
            $db = $this->app['db']->connection('sqlite');
            $pdo = $db->getPdo();

            // Enable WAL mode (Write-Ahead Logging)
            // Allows concurrent reads and writes - much faster for desktop apps
            $pdo->exec('PRAGMA journal_mode=WAL');

            // Enable foreign key constraints
            $pdo->exec('PRAGMA foreign_keys=ON');

            // Performance optimizations
            // NORMAL = good balance between speed and safety
            $pdo->exec('PRAGMA synchronous=NORMAL');

            // Set cache size to 2MB (negative value = KB)
            $pdo->exec('PRAGMA cache_size=-2000');

            // Use memory for temp tables (faster than disk)
            $pdo->exec('PRAGMA temp_store=MEMORY');

            // Enable memory-mapped I/O (64MB)
            $pdo->exec('PRAGMA mmap_size=67108864');

            // Optimize WAL checkpoint
            $pdo->exec('PRAGMA wal_autocheckpoint=1000');

        } catch (\Exception $e) {
            // Silently fail - app can still work without optimizations
            \Log::debug('SQLite optimization skipped: ' . $e->getMessage());
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
