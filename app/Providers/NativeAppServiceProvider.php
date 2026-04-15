<?php

namespace App\Providers;

use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\Window;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        // Load window and menu configuration
        if (file_exists(base_path('routes/native.php'))) {
            require base_path('routes/native.php');
        }
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
            'memory_limit' => '512M',
            'max_execution_time' => '300',
            'display_errors' => '0',
            'log_errors' => '1',
            'error_log' => storage_path('logs/native-php-error.log'),
        ];
    }
}
