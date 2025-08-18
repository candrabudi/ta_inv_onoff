<?php

namespace App\Exports;

use App\Models\Transaction;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TransactionsExport implements FromArray, WithHeadings, WithTitle
{
    public function __construct(public ?Carbon $start, public ?Carbon $end, public ?string $search) {}

    public function headings(): array
    {
        return ['Tanggal', 'User', 'Tipe', 'Total Harga'];
    }

    public function array(): array
    {
        $q = Transaction::with('user:id,name')->orderBy('created_at', 'desc');
        if ($this->start) $q->where('created_at', '>=', $this->start);
        if ($this->end)   $q->where('created_at', '<=', $this->end);
        if ($this->search) {
            $q->where(function ($qq) {
                $qq->whereHas('user', fn($u) => $u->where('name', 'like', "%{$this->search}%"))
                    ->orWhere('type', 'like', "%{$this->search}%");
            });
        }

        return $q->get()->map(function ($t) {
            return [
                $t->created_at->format('Y-m-d H:i:s'),
                $t->user?->name,
                $t->type,
                (float)$t->total_price,
            ];
        })->toArray();
    }

    public function title(): string
    {
        return 'Transaksi';
    }
}
