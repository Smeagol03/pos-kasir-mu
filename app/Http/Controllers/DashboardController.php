<?php

namespace App\Http\Controllers;

use App\Repositories\ProductRepository;
use App\Repositories\TransactionRepository;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private ProductRepository $productRepository
    ) {}

    public function index(): View
    {
        // Real-time dashboard data without caching
        $todayStats = $this->transactionRepository->getDailyStats(now());
        
        $monthStats = $this->transactionRepository->getMonthlyStats(now());
        
        $recentTransactions = $this->transactionRepository->getRecent(5);
        
        $lowStockProducts = $this->productRepository->getLowStock(10, 5);
        
        $topProducts = $this->transactionRepository->getTopProducts(5);
        
        $revenueChartData = $this->transactionRepository->getRevenueLast7Days();

        return view('dashboard', compact(
            'todayStats',
            'monthStats',
            'recentTransactions',
            'lowStockProducts',
            'topProducts',
            'revenueChartData'
        ));
    }
}
