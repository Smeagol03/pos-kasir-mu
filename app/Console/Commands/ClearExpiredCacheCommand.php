<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearExpiredCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear expired cache entries to free up memory';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // For file-based cache, we can clear old files
        if (config('cache.default') === 'file') {
            $cachePath = storage_path('framework/cache/data');
            $this->info('Clearing expired cache files...');
            
            $files = glob($cachePath . '/*');
            $cleared = 0;
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    $contents = file_get_contents($file);
                    if ($contents !== false) {
                        $data = unserialize($contents);
                        if (is_array($data) && isset($data['expires_at']) && $data['expires_at'] < time()) {
                            unlink($file);
                            $cleared++;
                        }
                    }
                }
            }
            
            $this->info("Cleared {$cleared} expired cache files.");
        } else {
            $this->info('Cache driver is not file-based, skipping manual expiration check.');
        }
        
        $this->info('Cache cleanup completed.');
    }
}