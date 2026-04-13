<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RestoreDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restore:database {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore the database from a backup file';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $filename = $this->argument('filename');
        $backupPath = storage_path('backups/' . $filename);

        // Check if the backup file exists
        if (!file_exists($backupPath)) {
            $this->error("Backup file does not exist: {$backupPath}");
            return;
        }

        $this->info("Restoring database from: {$backupPath}");

        if (!$this->confirm('This will overwrite the current database. Do you want to continue?')) {
            $this->info('Restore cancelled.');
            return;
        }

        // Determine the database connection
        $dbConnection = config('database.default');
        $dbConfig = config("database.connections.{$dbConnection}");

        try {
            // Decompress if it's a gzipped file
            $tempFile = null;
            if (substr($backupPath, -3) === '.gz') {
                $tempFile = storage_path('app/temp_restore.sql');
                $command = sprintf('gunzip -c %s > %s', escapeshellarg($backupPath), escapeshellarg($tempFile));
                shell_exec($command);
                $restoreSource = $tempFile;
            } else {
                $restoreSource = $backupPath;
            }

            // Build the restore command
            $command = '';
            if ($dbConnection === 'mysql') {
                $command = sprintf(
                    'mysql --host=%s --port=%s --user=%s --password=%s --database=%s < %s',
                    escapeshellarg($dbConfig['host']),
                    escapeshellarg($dbConfig['port']),
                    escapeshellarg($dbConfig['username']),
                    escapeshellarg($dbConfig['password']),
                    escapeshellarg($dbConfig['database']),
                    escapeshellarg($restoreSource)
                );
            } elseif ($dbConnection === 'sqlite') {
                $command = sprintf(
                    'sqlite3 %s ".read %s"',
                    escapeshellarg($dbConfig['database']),
                    escapeshellarg($restoreSource)
                );
            } else {
                $this->error("Unsupported database type: {$dbConnection}");
                return;
            }

            // Execute the restore command
            $exitCode = shell_exec($command . ' 2>&1; echo $?');

            // Clean up temporary file if it was created
            if ($tempFile && file_exists($tempFile)) {
                unlink($tempFile);
            }

            $this->info('Database restored successfully!');
            
            // Clear cache and config after restore
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            $this->info('Configuration and cache cleared.');
            
            // Log the restore
            \Log::info('Database restored', [
                'filename' => $filename,
                'path' => $backupPath
            ]);
        } catch (\Exception $e) {
            $this->error('Error during restore: ' . $e->getMessage());
            \Log::error('Database restore failed', [
                'error' => $e->getMessage(),
                'filename' => $filename
            ]);
        }
    }
}