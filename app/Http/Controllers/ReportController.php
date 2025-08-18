<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockHistory;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockSummaryExport;
use App\Exports\StockHistoryExport;
use App\Exports\TransactionsExport;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function stockSummary(Request $request)
    {
        [$start, $end] = $this->parseRange($request);
        $products = Product::select('id', 'name', 'sku', 'price', 'stock')->orderBy('name')->get();

        $summary = $products->map(function ($p) use ($start, $end) {
            $openingChange = StockHistory::where('product_id', $p->id)
                ->when($start, fn($q) => $q->where('created_at', '<', $start))
                ->sum('quantity_change');
            $in = StockHistory::where('product_id', $p->id)
                ->when($start, fn($q) => $q->where('created_at', '>=', $start))
                ->when($end,   fn($q) => $q->where('created_at', '<=', $end))
                ->where('type', 'in')
                ->sum('quantity_change');

            $out = StockHistory::where('product_id', $p->id)
                ->when($start, fn($q) => $q->where('created_at', '>=', $start))
                ->when($end,   fn($q) => $q->where('created_at', '<=', $end))
                ->where('type', 'out')
                ->sum('quantity_change');

            $adj = StockHistory::where('product_id', $p->id)
                ->when($start, fn($q) => $q->where('created_at', '>=', $start))
                ->when($end,   fn($q) => $q->where('created_at', '<=', $end))
                ->where('type', 'adjustment')
                ->sum('quantity_change');

            $opening = (int) $openingChange;
            $closing = (int) ($opening + $in - $out + $adj);

            return [
                'product_id'   => $p->id,
                'name'         => $p->name,
                'sku'          => $p->sku,
                'price'        => $p->price,
                'opening'      => $opening,
                'in'           => (int) $in,
                'out'          => (int) $out,
                'adjustment'   => (int) $adj,
                'closing'      => $closing,
                'current'      => (int) $p->stock,
                'mismatch'     => $closing !== (int)$p->stock,
            ];
        });

        return response()->json([
            'data'       => $summary,
            'start_date' => $start?->toDateString(),
            'end_date'   => $end?->toDateString(),
        ]);
    }

    public function stockHistory(Request $request)
    {
        [$start, $end] = $this->parseRange($request);
        $search = $request->get('search');

        $q = StockHistory::with(['product:id,name,sku', 'user:id,name'])
            ->orderBy('created_at', 'desc');

        if ($start) $q->where('created_at', '>=', $start);
        if ($end)   $q->where('created_at', '<=', $end);
        if ($search) {
            $q->whereHas('product', function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        return response()->json($q->get());
    }

    public function transactions(Request $request)
    {
        [$start, $end] = $this->parseRange($request);
        $search = $request->get('search');

        $q = Transaction::with('user:id,name')
            ->orderBy('created_at', 'desc');

        if ($start) $q->where('created_at', '>=', $start);
        if ($end)   $q->where('created_at', '<=', $end);

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->whereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }
        return response()->json($q->get());
    }

    public function exportStockSummary(Request $request)
    {
        [$start, $end] = $this->parseRange($request);
        return Excel::download(new StockSummaryExport($start, $end), $this->fileName('stock_summary', $start, $end));
    }

    public function exportStockHistory(Request $request)
    {
        [$start, $end] = $this->parseRange($request);
        $search = $request->get('search');
        return Excel::download(new StockHistoryExport($start, $end, $search), $this->fileName('stock_history', $start, $end));
    }

    public function exportTransactions(Request $request)
    {
        [$start, $end] = $this->parseRange($request);
        $search = $request->get('search');
        return Excel::download(new TransactionsExport($start, $end, $search), $this->fileName('transactions', $start, $end));
    }


    private function parseRange(Request $request): array
    {
        $start = $request->get('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : null;
        $end   = $request->get('end_date')   ? Carbon::parse($request->get('end_date'))->endOfDay()   : null;
        return [$start, $end];
    }

    private function fileName(string $prefix, ?Carbon $start, ?Carbon $end): string
    {
        $suffix = '';
        if ($start && $end) $suffix = "_{$start->toDateString()}_{$end->toDateString()}";
        return "{$prefix}{$suffix}.xlsx";
    }
}
