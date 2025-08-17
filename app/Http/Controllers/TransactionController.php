<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockHistory;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index()
    {
        return view('transactions.index');
    }

    public function list(Request $request)
    {
        $query = Transaction::with('user')->orderBy('created_at', 'desc');

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $perPage = $request->per_page ?? 10;
        $transactions = $query->paginate($perPage);

        return response()->json($transactions);
    }


    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:online,offline',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {

            $productIds = collect($request->products)->pluck('id');
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            $total = 0;
            foreach ($request->products as $p) {
                $product = $products[$p['id']];
                if ($product->stock < $p['quantity']) {
                    throw new \Exception("Stok produk {$product->name} tidak cukup!");
                }
                $total += $product->price * $p['quantity'];
            }

            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'type' => $request->type,
                'total_price' => $total
            ]);

            foreach ($request->products as $p) {
                $product = $products[$p['id']];
                $subtotal = $product->price * $p['quantity'];

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'quantity' => $p['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal
                ]);

                $product->decrement('stock', $p['quantity']);

                StockHistory::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'quantity_change' => -$p['quantity'],
                    'type' => 'out',
                    'note' => "Transaksi ID {$transaction->id}"
                ]);
            }
        });

        return response()->json(['success' => true]);
    }

    public function destroy(Transaction $transaction)
    {
        DB::transaction(function () use ($transaction) {
            foreach ($transaction->details as $detail) {
                $product = $detail->product;
                $product->increment('stock', $detail->quantity);

                StockHistory::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'quantity_change' => $detail->quantity,
                    'type' => 'adjustment',
                    'note' => "Hapus transaksi ID {$transaction->id}"
                ]);
            }

            $transaction->delete();
        });

        return response()->json(['success' => true]);
    }

    public function details(Transaction $transaction)
    {
        $details = $transaction->details()->with('product')->get()->map(function ($d) {
            return [
                'product' => $d->product,
                'price' => $d->price,
                'quantity' => $d->quantity,
                'subtotal' => $d->subtotal
            ];
        });

        return response()->json($details);
    }
}
