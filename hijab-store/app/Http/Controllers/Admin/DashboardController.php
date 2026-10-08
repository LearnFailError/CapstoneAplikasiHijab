<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'productCount' => Product::count(),
            'pendingOrders' => Order::where('status', 'pending')->count(),
            'recentOrders' => Order::latest()->limit(8)->get(),
        ]);
    }
}
