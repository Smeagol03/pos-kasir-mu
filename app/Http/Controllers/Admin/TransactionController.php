<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Repositories\TransactionRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    public function __construct(private TransactionRepository $transactionRepository) {}

    public function index(Request $request): View
    {
        $startDate = $request->get('start_date') ? now()->parse($request->get('start_date')) : now()->startOfMonth();
        $endDate = $request->get('end_date') ? now()->parse($request->get('end_date')) : now();

        $invoice = $request->get('invoice');
        $transactions = $this->transactionRepository->paginateByDateRange($startDate, $endDate, 15, $invoice);
        $stats = [
            'count' => $transactions->total(),
            'total' => $this->transactionRepository->getTotalByDateRange($startDate, $endDate),
        ];

        return view('admin.transactions.index', compact('transactions', 'stats', 'startDate', 'endDate'));
    }

    public function show(int $id): View
    {
        $transaction = $this->transactionRepository->findOrFail($id);

        return view('admin.transactions.show', compact('transaction'));
    }

    public function export(Request $request): StreamedResponse
    {
        $startDate = $request->get('start_date') ? now()->parse($request->get('start_date')) : now()->startOfMonth();
        $endDate = $request->get('end_date') ? now()->parse($request->get('end_date')) : now();

        $transactions = $this->transactionRepository->getByDateRange($startDate, $endDate);

        ActivityLog::log('Export Transaksi', "Mengexport data transaksi periode {$startDate->format('d/m/Y')} - {$endDate->format('d/m/Y')}");

        $filename = 'transaksi_'.$startDate->format('Ymd').'_'.$endDate->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, ['Invoice', 'Tanggal', 'Kasir', 'Total', 'Tunai', 'Kembalian']);

            // Data
            foreach ($transactions as $transaction) {
                fputcsv($handle, [
                    $transaction->invoice_code,
                    $transaction->created_at->format('Y-m-d H:i:s'),
                    $transaction->user->name ?? 'N/A',
                    $transaction->total,
                    $transaction->cash,
                    $transaction->change,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
