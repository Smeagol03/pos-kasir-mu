<?php

namespace App\Services;

use Native\Laravel\Facades\Notification;

class NativeNotificationService
{
    /**
     * Show a simple notification.
     */
    public function show(string $title, string $message): void
    {
        Notification::new()
            ->title($title)
            ->message($message)
            ->show();
    }

    /**
     * Show notification with action button.
     */
    public function showWithAction(string $title, string $message, string $actionLabel, string $event): void
    {
        Notification::new()
            ->title($title)
            ->message($message)
            ->addAction($actionLabel)
            ->event($event)
            ->show();
    }

    /**
     * Show low stock alert notification.
     */
    public function lowStockAlert(int $count): void
    {
        Notification::new()
            ->title('⚠️ Stok Rendah')
            ->message("{$count} produk memiliki stok rendah")
            ->hasReply(false)
            ->event('low-stock.check')
            ->show();
    }

    /**
     * Show transaction success notification.
     */
    public function transactionSuccess(string $invoiceCode, string $total): void
    {
        Notification::new()
            ->title('✅ Transaksi Berhasil')
            ->message("Invoice: {$invoiceCode} - Total: {$total}")
            ->event('transaction.success')
            ->show();
    }

    /**
     * Show error notification.
     */
    public function error(string $title, string $message): void
    {
        Notification::new()
            ->title('❌ ' . $title)
            ->message($message)
            ->show();
    }

    /**
     * Show info notification.
     */
    public function info(string $title, string $message): void
    {
        Notification::new()
            ->title('ℹ️ ' . $title)
            ->message($message)
            ->show();
    }

    /**
     * Show backup completed notification.
     */
    public function backupCompleted(string $filename): void
    {
        Notification::new()
            ->title('💾 Backup Selesai')
            ->message("Database berhasil di-backup: {$filename}")
            ->event('backup.completed')
            ->show();
    }

    /**
     * Show restore completed notification.
     */
    public function restoreCompleted(string $filename): void
    {
        Notification::new()
            ->title('🔄 Restore Selesai')
            ->message("Database berhasil di-restore dari: {$filename}")
            ->event('restore.completed')
            ->show();
    }
}
