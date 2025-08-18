<?php

namespace App\Exports;

use App\Models\StockHistory;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class StockHistoryExport implements FromArray, WithHeadings, WithTitle
{
    public function __construct(public ?Carbon $start, public ?Carbon $end, public ?string $search) {}

    public function headings(): array
    {
        return ['Tanggal', 'Produk', 'SKU', 'User', 'Perubahan', 'Tipe', 'Catatan'];
    }

    public function array(): array
    {
        $q = StockHistory::with(['product:id,name,sku', 'user:id,name'])
            ->orderBy('created_at', 'desc');

        if ($this->start) $q->where('created_at', '>=', $this->start);
        if ($this->end)   $q->where('created_at', '<=', $this->end);
        if ($this->search) {
            $q->whereHas('product', function ($qq) {
                $qq->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%");
            });
        }

        return $q->get()->map(function ($h) {
            return [
                $h->created_at->format('Y-m-d H:i:s'),
                $h->product?->name,
                $h->product?->sku,
                $h->user?->name,
                (int)$h->quantity_change,
                $h->type,
                $h->note,
            ];
        })->toArray();
    }

    public function title(): string
    {
        return 'History Stok';
    }
}
