<?php

namespace App\Services;

use Native\Laravel\Facades\System;

class PrinterService
{
    /**
     * Get all available printers.
     * 
     * @return array<\stdClass>
     */
    public function getAvailablePrinters(): array
    {
        return System::printers();
    }

    /**
     * Get default printer.
     */
    public function getDefaultPrinter(): ?\stdClass
    {
        $printers = $this->getAvailablePrinters();
        return collect($printers)->first(fn($printer) => $printer->isDefault);
    }

    /**
     * Print receipt HTML to printer.
     */
    public function printReceipt(string $html, $printer = null): bool
    {
        $targetPrinter = $printer ?? $this->getDefaultPrinter();

        if (!$targetPrinter) {
            \Log::warning('No printer available for receipt printing');
            return false;
        }

        try {
            System::print($html, $targetPrinter);
            \Log::info('Receipt printed successfully', ['printer' => $targetPrinter->name]);
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to print receipt', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Export receipt to PDF.
     * 
     * @return string PDF content as base64 or file path
     */
    public function exportReceiptPDF(string $html, string $filename = null): string
    {
        try {
            $pdfBase64 = System::printToPDF($html, [
                'landscape' => false,
                'pageSize' => 'A5',
                'marginsType' => 1, // no margins
                'printBackground' => true,
            ]);

            if ($filename) {
                // Ensure directory exists
                $directory = storage_path('receipts');
                if (!is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }

                $path = $directory . DIRECTORY_SEPARATOR . $filename;
                file_put_contents($path, base64_decode($pdfBase64));
                
                \Log::info('Receipt PDF exported', ['path' => $path]);
                return $path;
            }

            return $pdfBase64;
        } catch (\Exception $e) {
            \Log::error('Failed to export PDF', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Print transaction receipt.
     */
    public function printTransactionReceipt($transaction): bool
    {
        // Generate receipt HTML from view
        $html = view('kasir.pos.receipt-print', compact('transaction'))->render();

        return $this->printReceipt($html);
    }

    /**
     * Export transaction receipt to PDF.
     */
    public function exportTransactionReceiptPDF($transaction, string $filename = null): string
    {
        $html = view('kasir.pos.receipt-print', compact('transaction'))->render();
        
        $filename = $filename ?? 'receipt-' . $transaction->invoice_code . '.pdf';
        
        return $this->exportReceiptPDF($html, $filename);
    }
}
