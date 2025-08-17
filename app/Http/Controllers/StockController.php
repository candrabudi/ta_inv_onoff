<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index()
    {
        return view('stocks.index');
    }

    public function list(Request $request)
    {
        $query = $request->search;
        if ($query) {
            $products = Product::where('name', 'like', "%{$query}%")
                ->orWhere('sku', 'like', "%{$query}%")
                ->get();
        } else {
            $products = Product::all();
        }
        return response()->json($products);
    }

    public function restock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string|max:255'
        ]);

        DB::transaction(function () use ($request) {
            $product = Product::find($request->product_id);
            $product->increment('stock', $request->quantity);

            StockHistory::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'quantity_change' => $request->quantity,
                'type' => 'in',
                'note' => $request->note ?? 'Restock'
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Produk berhasil di-restock']);
    }

    public function history($product_id)
    {
        $histories = StockHistory::with('user')
            ->where('product_id', $product_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($histories);
    }
}
