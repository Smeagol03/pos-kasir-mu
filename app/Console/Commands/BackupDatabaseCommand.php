<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database {--filename=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the database to a compressed file';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting database backup...');

        // Generate filename if not provided
        $filename = $this->option('filename') ?: 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql.gz';
        
        // Ensure backups directory exists
        $backupPath = storage_path('backups');
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $fullPath = $backupPath . '/' . $filename;

        try {
            // Determine the database connection
            $dbConnection = config('database.default');
            $dbConfig = config("database.connections.{$dbConnection}");

            // Build the mysqldump command
            $command = '';
            if ($dbConnection === 'mysql') {
                $command = sprintf(
                    'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s | gzip > %s',
                    escapeshellarg($dbConfig['host']),
                    escapeshellarg($dbConfig['port']),
                    escapeshellarg($dbConfig['username']),
                    escapeshellarg($dbConfig['password']),
                    escapeshellarg($dbConfig['database']),
                    escapeshellarg($fullPath)
                );
            } elseif ($dbConnection === 'sqlite') {
                $command = sprintf(
                    'sqlite3 %s ".backup %s" && gzip %s',
                    escapeshellarg($dbConfig['database']),
                    escapeshellarg(str_replace('.gz', '', $fullPath)),
                    escapeshellarg(str_replace('.gz', '', $fullPath))
                );
            } else {
                $this->error("Unsupported database type: {$dbConnection}");
                return;
            }

            // Execute the backup command
            $exitCode = shell_exec($command . ' 2>&1; echo $?');
            
            // Check if the backup file was created successfully
            if (file_exists($fullPath) && filesize($fullPath) > 0) {
                $size = round(filesize($fullPath) / 1024 / 1024, 2); // Size in MB
                $this->info("Database backup completed successfully!");
                $this->info("Backup saved to: {$fullPath}");
                $this->info("File size: {$size} MB");
                
                // Log the backup
                \Log::info('Database backup created', [
                    'filename' => $filename,
                    'size_mb' => $size,
                    'path' => $fullPath
                ]);
            } else {
                $this->error('Failed to create database backup.');
            }
        } catch (\Exception $e) {
            $this->error('Error during backup: ' . $e->getMessage());
            \Log::error('Database backup failed', [
                'error' => $e->getMessage(),
                'filename' => $filename
            ]);
        }
    }
}