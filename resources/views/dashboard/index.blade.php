@extends('template.app')
@section('title', 'Dashboard')
@section('content')
    <div class="container-fluid">

        <!-- Statistik Utama -->
        <div class="row">

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Produk</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalProducts }}</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Transaksi</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalTransactions }}</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Stok</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalStock }}</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Kategori</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalCategories }}</div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Users dan Revenue -->
        @if (Auth::user()->role === 'admin')
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-dark shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalUsers }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-secondary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Total Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp
                                {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Ringkasan Produk -->
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Ringkasan Produk</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Nama Produk</th>
                                        <th>Kategori</th>
                                        <th>Harga</th>
                                        <th>Stok</th>
                                        <th>Safety Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (\App\Models\Product::with('category')->get() as $product)
                                        <tr>
                                            <td>{{ $product->name }}</td>
                                            <td>{{ $product->category->name ?? '-' }}</td>
                                            <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                            <td>{{ $product->stock }}</td>
                                            <td>{{ $product->safety_stock }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Produk Stok Menipis & Transaksi Terbaru -->
        <div class="row mt-4">
            <!-- Stok Menipis -->
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-danger">Produk Stok Menipis</h6>
                    </div>
                    <div class="card-body">
                        @if ($lowStockProducts->count())
                            <ul>
                                @foreach ($lowStockProducts as $p)
                                    <li>{{ $p->name }} (Stok: {{ $p->stock }}, Safety: {{ $p->safety_stock }})
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p>Tidak ada produk dengan stok menipis.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Transaksi Terbaru -->
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Transaksi Terbaru</h6>
                    </div>
                    <div class="card-body">
                        @if ($recentTransactions->count())
                            <ul>
                                @foreach ($recentTransactions as $t)
                                    <li>
                                        {{ $t->user->name ?? '-' }} |
                                        Total: Rp {{ number_format($t->total_price, 0, ',', '.') }} |
                                        {{ $t->created_at->format('d-m-Y H:i') }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p>Tidak ada transaksi terbaru.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
