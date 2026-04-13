<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ListBackupsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all available database backups';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $backupPath = storage_path('backups');
        
        if (!is_dir($backupPath)) {
            $this->info('No backups directory found.');
            return;
        }

        $files = scandir($backupPath);
        $backups = array_filter($files, function ($file) {
            return $file !== '.' && $file !== '..' && (substr($file, -4) === '.sql' || substr($file, -7) === '.sql.gz');
        });

        if (empty($backups)) {
            $this->info('No backups found.');
            return;
        }

        $this->info('Available backups:');
        $this->table(['Filename', 'Size (MB)', 'Modified'], collect($backups)->map(function ($file) use ($backupPath) {
            $filePath = $backupPath . '/' . $file;
            $size = round(filesize($filePath) / 1024 / 1024, 2);
            $modified = date('Y-m-d H:i:s', filemtime($filePath));
            return [$file, $size, $modified];
        })->toArray());
    }
}