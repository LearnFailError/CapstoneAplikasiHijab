<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        return view('admin.orders.index', ['orders' => Order::latest()->paginate(20)]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', ['order' => $order->load('items')]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,processing,shipped,completed,cancelled'],
            'payment_status' => ['required', 'in:unpaid,paid'],
        ]);

        DB::transaction(function () use ($validated, $order): void {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status === 'cancelled' && $validated['status'] !== 'cancelled') {
                throw ValidationException::withMessages([
                    'status' => 'Pesanan yang dibatalkan tidak dapat diaktifkan kembali.',
                ]);
            }

            if ($validated['status'] === 'cancelled' && $lockedOrder->status !== 'cancelled') {
                foreach ($lockedOrder->items as $item) {
                    if ($item->product_id) {
                        Product::whereKey($item->product_id)
                            ->lockForUpdate()
                            ->first()
                            ?->increment('stock', $item->quantity);
                    }
                }
            }

            $lockedOrder->update($validated);
        });

        return redirect()->route('admin.orders.show', $order)->with('status', 'Status pesanan berhasil diperbarui.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== 'cancelled' || $lockedOrder->payment_status !== 'unpaid') {
                throw ValidationException::withMessages([
                    'order' => 'Hanya pesanan batal yang belum dibayar yang dapat dihapus.',
                ]);
            }

            $lockedOrder->delete();
        });

        return redirect()->route('admin.orders.index')->with('status', 'Pesanan yang dibatalkan berhasil dihapus.');
    }
}
