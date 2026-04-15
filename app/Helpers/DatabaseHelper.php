<?php

namespace App\Helpers;

class DatabaseHelper
{
    /**
     * Get OS-specific user data directory for desktop app.
     * 
     * Windows: %APPDATA%/PosKasir
     * macOS: ~/Library/Application Support/PosKasir
     * Linux: ~/.local/share/PosKasir
     */
    public static function getUserDataPath(): string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $basePath = getenv('APPDATA') ?: sys_get_temp_dir();
            $separator = '\\';
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            $basePath = getenv('HOME') . '/Library/Application Support';
            $separator = '/';
        } else {
            $basePath = getenv('HOME') . '/.local/share';
            $separator = '/';
        }

        return $basePath . $separator . 'PosKasir';
    }

    /**
     * Get database file path for desktop app.
     */
    public static function getDatabasePath(): string
    {
        $userDataPath = self::getUserDataPath();
        $databasePath = $userDataPath . DIRECTORY_SEPARATOR . 'database.sqlite';

        // Create user data directory if it doesn't exist
        if (!is_dir($userDataPath)) {
            mkdir($userDataPath, 0755, true);
        }

        return $databasePath;
    }

    /**
     * Ensure database file exists.
     */
    public static function ensureDatabaseExists(): void
    {
        $path = self::getDatabasePath();

        if (!file_exists($path)) {
            // Create empty SQLite database
            file_put_contents($path, '');

            // Set proper permissions
            if (PHP_OS_FAMILY !== 'Windows') {
                chmod($path, 0644);
            }
        }
    }

    /**
     * Get backup directory path.
     */
    public static function getBackupPath(): string
    {
        $userDataPath = self::getUserDataPath();
        $backupPath = $userDataPath . DIRECTORY_SEPARATOR . 'backups';

        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        return $backupPath;
    }

    /**
     * Get logs directory path.
     */
    public static function getLogsPath(): string
    {
        $userDataPath = self::getUserDataPath();
        $logsPath = $userDataPath . DIRECTORY_SEPARATOR . 'logs';

        if (!is_dir($logsPath)) {
            mkdir($logsPath, 0755, true);
        }

        return $logsPath;
    }
}
