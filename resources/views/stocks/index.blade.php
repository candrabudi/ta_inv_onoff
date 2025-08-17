@extends('template.app')

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Stok Produk</h6>
            <div>
                <input type="text" id="searchInput" class="form-control d-inline-block" style="width: 250px;"
                    placeholder="Cari produk...">
                <button class="btn btn-primary ml-2" id="reloadStock">Refresh</button>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered" id="stocksTable">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>Nama Produk</th>
                        <th>SKU</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Modal Restock -->
    <div class="modal fade" id="restockModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Restock Produk</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="restockProductId">
                    <div class="form-group">
                        <label>Nama Produk</label>
                        <input type="text" id="restockProductName" class="form-control" disabled>
                    </div>
                    <div class="form-group">
                        <label>Jumlah Restock</label>
                        <input type="number" min="1" id="restockQuantity" class="form-control" value="1">
                    </div>
                    <div class="form-group">
                        <label>Catatan</label>
                        <input type="text" id="restockNote" class="form-control" placeholder="Opsional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" id="saveRestock">Simpan</button>
                    <button class="btn btn-secondary" data-dismiss="modal">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal History -->
    <div class="modal fade" id="historyModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">History Stok</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered" id="historyTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>User</th>
                                <th>Perubahan</th>
                                <th>Tipe</th>
                                <th>Catatan</th>
                                <th>Tanggal</th>
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
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            let debounceTimer;

            function fetchStocks() {
                let query = $('#searchInput').val();
                $.get("{{ route('stocks.list') }}", {
                    search: query
                }, function(data) {
                    let rows = '';
                    data.forEach((p, index) => {
                        rows += `<tr>
                    <td>${index+1}</td>
                    <td>${p.name}</td>
                    <td>${p.sku}</td>
                    <td>${Number(p.price).toLocaleString('id-ID',{style:'currency',currency:'IDR'})}</td>
                    <td>${p.stock}</td>
                    <td>
                        <button class="btn btn-sm btn-success restockBtn" data-id="${p.id}" data-name="${p.name}">Restock</button>
                        <button class="btn btn-sm btn-info historyBtn" data-id="${p.id}">History</button>
                    </td>
                </tr>`;
                    });
                    $('#stocksTable tbody').html(rows);
                });
            }

            // Debounce search input
            $('#searchInput').on('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchStocks();
                }, 400);
            });

            $('#reloadStock').click(function() {
                $('#searchInput').val('');
                fetchStocks();
            });

            // Open restock modal
            $(document).on('click', '.restockBtn', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                $('#restockProductId').val(id);
                $('#restockProductName').val(name);
                $('#restockQuantity').val(1);
                $('#restockNote').val('');
                $('#restockModal').modal('show');
            });

            // Save restock
            $('#saveRestock').click(function() {
                let id = $('#restockProductId').val();
                let qty = parseInt($('#restockQuantity').val());
                let note = $('#restockNote').val();

                if (qty < 1) {
                    Swal.fire('Peringatan', 'Jumlah restock minimal 1', 'warning');
                    return;
                }

                $.ajax({
                    url: "{{ route('stocks.restock') }}",
                    type: "POST",
                    data: {
                        product_id: id,
                        quantity: qty,
                        note: note,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        $('#restockModal').modal('hide');
                        fetchStocks();
                        Swal.fire('Berhasil', res.message, 'success');
                    },
                    error: function(xhr) {
                        let msg = '';
                        if (xhr.responseJSON.error) {
                            msg = xhr.responseJSON.error;
                        } else {
                            $.each(xhr.responseJSON.errors, function(k, v) {
                                msg += v + '<br>';
                            });
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: msg
                        });
                    }
                });
            });

            // Open history modal
            $(document).on('click', '.historyBtn', function() {
                let id = $(this).data('id');
                $('#historyTable tbody').html('');
                $.get(`/stocks/${id}/history`, function(data) {
                    let rows = '';
                    data.forEach((h, index) => {
                        rows += `<tr>
                    <td>${index+1}</td>
                    <td>${h.user.name}</td>
                    <td>${h.quantity_change}</td>
                    <td>${h.type}</td>
                    <td>${h.note || '-'}</td>
                    <td>${new Date(h.created_at).toLocaleString('id-ID',{timeZone:'Asia/Jakarta'})}</td>
                </tr>`;
                    });
                    $('#historyTable tbody').html(rows);
                    $('#historyModal').modal('show');
                });
            });

            // Initial fetch
            fetchStocks();
        });
    </script>
@endpush
