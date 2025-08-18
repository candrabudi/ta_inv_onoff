@extends('template.app')
@section('title', 'Data Transaksi')
@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Transaksi</h6>
            <div class="d-flex">
                <input type="text" id="daterange" class="form-control" placeholder="Filter Tanggal">
                <button class="btn btn-secondary btn-sm ml-2" id="resetFilter">Reset</button>
            </div>
        </div>
        <div class="card-body">
            <button class="btn btn-primary mb-3" id="addTransactionBtn">Tambah Transaksi</button>

            <table class="table table-bordered" id="transactionsTable">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>Pengguna</th>
                        <th>Tipe</th>
                        <th>Total Harga</th>
                        <th>Tanggal</th>
                        <th>Detail</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="transactionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Transaksi</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tipe Transaksi</label>
                        <select id="transactionType" class="form-control">
                            <option value="offline">Offline</option>
                            <option value="online">Online</option>
                        </select>
                    </div>

                    <table class="table table-bordered" id="transactionProducts">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" id="saveTransactionBtn">Simpan</button>
                    <button class="btn btn-secondary" data-dismiss="modal">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="transactionDetailModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Transaksi</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered" id="transactionDetailTable">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            let startDate = null;
            let endDate = null;
            let perPage = 10;

            function formatTanggal(tgl) {
                return new Date(tgl).toLocaleString('id-ID', {
                    timeZone: 'Asia/Jakarta'
                });
            }

            function fetchTransactions(page = 1) {
                let url = "{{ route('transactions.list') }}?page=" + page + "&per_page=" + perPage;
                if (startDate && endDate) url += `&start_date=${startDate}&end_date=${endDate}`;

                $.get(url, function(res) {
                    renderTable(res.data, res.from);
                    renderPagination(res);
                });
            }

            function renderTable(data, startIndex) {
                let rows = '';
                data.forEach(function(tx, index) {
                    rows += `<tr>
                <td>${startIndex + index}</td>
                <td>${tx.user.name}</td>
                <td>${tx.type}</td>
                <td>${Number(tx.total_price).toLocaleString('id-ID',{style:'currency',currency:'IDR'})}</td>
                <td>${formatTanggal(tx.created_at)}</td>
                <td><button class="btn btn-sm btn-info detailBtn" data-id="${tx.id}">Lihat</button></td>
                <td><button class="btn btn-sm btn-danger deleteBtn" data-id="${tx.id}">Hapus</button></td>
            </tr>`;
                });
                $('#transactionsTable tbody').html(rows);
            }

            function renderPagination(res) {
                let totalPages = res.last_page;
                let currentPage = res.current_page;
                let pagination = '<nav class="mt-3"><ul class="pagination justify-content-center">';

                if (currentPage > 1)
                    pagination +=
                    `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage-1}">&laquo;</a></li>`;
                else
                    pagination += `<li class="page-item disabled"><span class="page-link">&laquo;</span></li>`;

                for (let i = 1; i <= totalPages; i++) {
                    let active = i === currentPage ? 'active' : '';
                    pagination +=
                        `<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                }

                if (currentPage < totalPages)
                    pagination +=
                    `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage+1}">&raquo;</a></li>`;
                else
                    pagination += `<li class="page-item disabled"><span class="page-link">&raquo;</span></li>`;

                pagination += '</ul></nav>';

                $('#transactionsPagination').remove();
                $('#transactionsTable').after(`<div id="transactionsPagination">${pagination}</div>`);
            }

            $('#daterange').daterangepicker({
                locale: {
                    format: 'YYYY-MM-DD',
                    applyLabel: "Terapkan",
                    cancelLabel: "Batal"
                }
            }, function(start, end) {
                startDate = start.format('YYYY-MM-DD');
                endDate = end.format('YYYY-MM-DD');
                fetchTransactions();
            });

            $('#resetFilter').click(function() {
                startDate = null;
                endDate = null;
                $('#daterange').val('');
                fetchTransactions();
            });

            $(document).on('click', '.pagination .page-link', function(e) {
                e.preventDefault();
                let page = $(this).data('page');
                if (page) fetchTransactions(page);
            });

            $('#addTransactionBtn').click(function() {
                $('#transactionType').val('offline');
                $('#transactionProducts tbody').html(
                    '<tr><td colspan="4" class="text-center">Memuat...</td></tr>');
                $.get("{{ route('products.list') }}", function(products) {
                    let rows = '';
                    products.forEach(function(p) {
                        rows += `<tr data-id="${p.id}">
                    <td>${p.name}</td>
                    <td>${Number(p.price).toLocaleString('id-ID',{style:'currency',currency:'IDR'})}</td>
                    <td class="productStock">${p.stock}</td>
                    <td><input type="number" min="0" max="${p.stock}" class="form-control quantityInput" value="0"></td>
                </tr>`;
                    });
                    $('#transactionProducts tbody').html(rows);
                    $('#transactionModal').modal('show');
                });
            });

            $('#saveTransactionBtn').click(function() {
                let products = [];
                $('.quantityInput').each(function() {
                    let qty = parseInt($(this).val());
                    if (qty > 0) products.push({
                        id: $(this).closest('tr').data('id'),
                        quantity: qty
                    });
                });

                if (!products.length) return Swal.fire('Peringatan', 'Pilih minimal 1 produk', 'warning');

                $.post("{{ route('transactions.store') }}", {
                    type: $('#transactionType').val(),
                    products: products,
                    _token: "{{ csrf_token() }}"
                }, function() {
                    $('#transactionModal').modal('hide');
                    fetchTransactions();
                    Swal.fire('Berhasil', 'Transaksi berhasil disimpan', 'success');
                });
            });

            $(document).on('click', '.deleteBtn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Apakah yakin?',
                    text: 'Transaksi akan dihapus!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/transactions/${id}`,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function() {
                                fetchTransactions();
                                Swal.fire('Terhapus', 'Transaksi berhasil dihapus',
                                    'success');
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.detailBtn', function() {
                let id = $(this).data('id');
                $('#transactionDetailTable tbody').html(
                    '<tr><td colspan="4" class="text-center">Memuat...</td></tr>');
                $.get(`/transactions/${id}/details`, function(details) {
                    let rows = '';
                    details.forEach(function(d) {
                        rows += `<tr>
                    <td>${d.product.name}</td>
                    <td>${Number(d.price).toLocaleString('id-ID',{style:'currency',currency:'IDR'})}</td>
                    <td>${d.quantity}</td>
                    <td>${Number(d.subtotal).toLocaleString('id-ID',{style:'currency',currency:'IDR'})}</td>
                </tr>`;
                    });
                    $('#transactionDetailTable tbody').html(rows);
                    $('#transactionDetailModal').modal('show');
                });
            });

            fetchTransactions();
        });
    </script>
@endpush
