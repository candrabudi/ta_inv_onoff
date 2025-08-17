<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $totalTransactions = Transaction::count();
        $totalStock = Product::sum('stock');
        $totalCategories = Category::count();
        $totalUsers = User::count();
        $totalRevenue = Transaction::sum('total_price');

        $lowStockProducts = Product::with('category')
            ->whereColumn('stock', '<=', 'safety_stock')
            ->get();

        $recentTransactions = Transaction::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'totalProducts',
            'totalTransactions',
            'totalStock',
            'totalCategories',
            'totalUsers',
            'totalRevenue',
            'lowStockProducts',
            'recentTransactions'
        ));
    }
}
