<?php

namespace App\Console\Commands;

use App\Notifications\LowStockAlert;
use App\Repositories\ProductRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckLowStockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-low-stock {--threshold=10 : Minimum stock threshold}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for products with low stock and send notifications';

    /**
     * Execute the console command.
     */
    public function handle(ProductRepository $productRepository): void
    {
        $threshold = $this->option('threshold');
        
        $this->info("Checking for products with stock below {$threshold}...");

        $lowStockProducts = $productRepository->getLowStock($threshold, 100); // Get up to 100 low stock products

        if ($lowStockProducts->isEmpty()) {
            $this->info('No products with low stock found.');
            return;
        }

        // Get admin users to notify
        $adminUsers = \App\Models\User::where('role', 'admin')->get();

        if ($adminUsers->isEmpty()) {
            $this->warn('No admin users found to notify.');
            return;
        }

        $notifiedCount = 0;
        foreach ($lowStockProducts as $product) {
            // Use Notification::send() to batch-send notifications efficiently
            Notification::send($adminUsers, new LowStockAlert($product));
            $notifiedCount++;

            $this->info("Notified about low stock for: {$product->name} ({$product->stock} remaining)");
        }

        $this->info("Sent notifications for {$notifiedCount} products with low stock.");
        
        \Log::info('Low stock check completed', [
            'threshold' => $threshold,
            'products_count' => $lowStockProducts->count(),
            'admin_users_notified' => $adminUsers->count()
        ]);
    }
}