<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\StockHistory;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class StockSummaryExport implements FromArray, WithHeadings, WithTitle
{
    public function __construct(public ?Carbon $start, public ?Carbon $end) {}

    public function headings(): array
    {
        return ['SKU', 'Nama Produk', 'Opening', 'Masuk', 'Keluar', 'Penyesuaian', 'Closing (periode)', 'Stok Saat Ini', 'Mismatch?'];
    }

    public function array(): array
    {
        $rows = [];
        $products = Product::select('id', 'name', 'sku', 'price', 'stock')->orderBy('name')->get();

        foreach ($products as $p) {
            $opening = StockHistory::where('product_id', $p->id)
                ->when($this->start, fn($q) => $q->where('created_at', '<', $this->start))
                ->sum('quantity_change');

            $in  = StockHistory::where('product_id', $p->id)->when($this->start, fn($q) => $q->where('created_at', '>=', $this->start))->when($this->end, fn($q) => $q->where('created_at', '<=', $this->end))->where('type', 'in')->sum('quantity_change');
            $out = StockHistory::where('product_id', $p->id)->when($this->start, fn($q) => $q->where('created_at', '>=', $this->start))->when($this->end, fn($q) => $q->where('created_at', '<=', $this->end))->where('type', 'out')->sum('quantity_change');
            $adj = StockHistory::where('product_id', $p->id)->when($this->start, fn($q) => $q->where('created_at', '>=', $this->start))->when($this->end, fn($q) => $q->where('created_at', '<=', $this->end))->where('type', 'adjustment')->sum('quantity_change');

            $closing = (int)$opening + (int)$in - (int)$out + (int)$adj;

            $rows[] = [
                $p->sku,
                $p->name,
                (int)$opening,
                (int)$in,
                (int)$out,
                (int)$adj,
                (int)$closing,
                (int)$p->stock,
                $closing !== (int)$p->stock ? 'YES' : 'NO',
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Ringkasan Stok';
    }
}
