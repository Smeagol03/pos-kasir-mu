<?php

namespace App\Services;

use Native\Laravel\Facades\Settings;

class AppSettingsService
{
    /**
     * Get a setting value.
     */
    public static function get(string $key, $default = null)
    {
        return Settings::get("app.{$key}", $default);
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, $value): void
    {
        Settings::set("app.{$key}", $value);
    }

    /**
     * Forget a setting.
     */
    public static function forget(string $key): void
    {
        Settings::forget("app.{$key}");
    }

    /**
     * Clear all app settings.
     */
    public static function clear(): void
    {
        Settings::clear();
    }

    /**
     * Get printer settings.
     */
    public static function getPrinterSettings(): array
    {
        return Settings::get('app.printer', [
            'auto_print' => false,
            'default_printer' => null,
            'receipt_width' => 58, // mm (58 or 80)
            'show_preview' => true,
        ]);
    }

    /**
     * Set printer settings.
     */
    public static function setPrinterSettings(array $settings): void
    {
        Settings::set('app.printer', $settings);
    }

    /**
     * Get window state.
     */
    public static function getWindowState(): array
    {
        return Settings::get('app.window', [
            'width' => 1400,
            'height' => 900,
            'x' => null,
            'y' => null,
            'maximized' => false,
        ]);
    }

    /**
     * Set window state.
     */
    public static function setWindowState(array $state): void
    {
        Settings::set('app.window', $state);
    }

    /**
     * Get user preferences.
     */
    public static function getPreferences(): array
    {
        return Settings::get('app.preferences', [
            'language' => 'id',
            'theme' => 'light',
            'sound_enabled' => true,
            'notifications_enabled' => true,
            'auto_backup' => true,
            'receipt_auto_print' => false,
            'low_stock_threshold' => 10,
        ]);
    }

    /**
     * Set user preferences.
     */
    public static function setPreferences(array $preferences): void
    {
        $current = self::getPreferences();
        Settings::set('app.preferences', array_merge($current, $preferences));
    }

    /**
     * Get last transaction state (for recovery).
     */
    public static function getLastTransaction(): ?array
    {
        return Settings::get('app.last_transaction', null);
    }

    /**
     * Set last transaction state.
     */
    public static function setLastTransaction(array $transaction): void
    {
        Settings::set('app.last_transaction', $transaction);
    }

    /**
     * Clear last transaction.
     */
    public static function clearLastTransaction(): void
    {
        Settings::forget('app.last_transaction');
    }
}
