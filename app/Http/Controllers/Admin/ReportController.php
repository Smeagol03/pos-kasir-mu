<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Repositories\TransactionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(protected TransactionRepository $transactionRepository) {}

    public function index(Request $request): View
    {
        $startDate = $request->has('start_date') ? Carbon::parse($request->get('start_date')) : now()->startOfMonth();
        $endDate = $request->has('end_date') ? Carbon::parse($request->get('end_date')) : now()->endOfMonth();

        $profitStats = $this->transactionRepository->getProfitStats($startDate, $endDate);
        $topProducts = $this->transactionRepository->getTopProducts(10);
        $dailyBreakdown = $this->transactionRepository->getDailyProfitBreakdown($startDate, $endDate);
        $transactionCount = $this->transactionRepository->getCountByDateRange($startDate, $endDate);

        // Additional analytics for enhanced reporting
        $avgTransactionValue = $transactionCount > 0 ? $profitStats['revenue'] / $transactionCount : 0;
        $bestDay = collect($dailyBreakdown)->sortByDesc('revenue')->first();
        $worstDay = collect($dailyBreakdown)->sortBy('revenue')->first();

        return view('admin.reports.index', compact(
            'profitStats', 
            'startDate', 
            'endDate', 
            'topProducts', 
            'dailyBreakdown', 
            'transactionCount',
            'avgTransactionValue',
            'bestDay',
            'worstDay'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $startDate = $request->has('start_date') ? Carbon::parse($request->get('start_date')) : now()->startOfMonth();
        $endDate = $request->has('end_date') ? Carbon::parse($request->get('end_date')) : now()->endOfMonth();

        $profitStats = $this->transactionRepository->getProfitStats($startDate, $endDate);
        $dailyBreakdown = $this->transactionRepository->getDailyProfitBreakdown($startDate, $endDate);
        $items = $this->transactionRepository->getTransactionItemsForExport($startDate, $endDate);

        ActivityLog::log('Export Laporan Accounting', "Mengexport laporan detail periode {$startDate->format('d/m/Y')} - {$endDate->format('d/m/Y')}");

        $filename = 'laporan_accounting_'.$startDate->format('Ymd').'_'.$endDate->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($profitStats, $items, $startDate, $endDate) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['LAPORAN AKUNTANSI DETAIL']);
            fputcsv($handle, ['Periode', $startDate->format('d/m/Y').' - '.$endDate->format('d/m/Y')]);
            fputcsv($handle, []);

            fputcsv($handle, ['RINGKASAN']);
            fputcsv($handle, ['Item', 'Nilai (IDR)']);
            fputcsv($handle, ['Total Omzet', $profitStats['revenue']]);
            fputcsv($handle, ['Total Modal (HPP)', $profitStats['cost']]);
            fputcsv($handle, ['Laba Bersih', $profitStats['profit']]);
            fputcsv($handle, []);

            fputcsv($handle, ['RINCIAN PER ITEM']);
            fputcsv($handle, ['Tanggal', 'Invoice', 'Produk', 'Qty', 'Harga Beli (Satuan)', 'Harga Jual (Satuan)', 'Total Modal', 'Total Jual', 'Laba']);

            foreach ($items as $item) {
                fputcsv($handle, [
                    $item->transaction->created_at->format('d/m/Y H:i'),
                    $item->transaction->invoice_code,
                    $item->product->name ?? 'N/A',
                    $item->quantity,
                    $item->purchase_price,
                    $item->price,
                    $item->purchase_price * $item->quantity,
                    $item->subtotal,
                    $item->subtotal - ($item->purchase_price * $item->quantity),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
