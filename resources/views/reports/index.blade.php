@extends('template.app')
@section('title', 'Data Laporan')
@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Laporan</h6>
        </div>
        <div class="card-body">

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-3" id="reportTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="stock-summary-tab" data-toggle="tab" href="#stock-summary"
                        role="tab">Ringkasan Stok</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="stock-history-tab" data-toggle="tab" href="#stock-history"
                        role="tab">History Stok</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="transactions-tab" data-toggle="tab" href="#transactions"
                        role="tab">Transaksi</a>
                </li>
            </ul>

            <div class="tab-content" id="reportTabsContent">

                {{-- RINGKASAN STOK --}}
                <div class="tab-pane fade show active" id="stock-summary" role="tabpanel">
                    <div class="d-flex align-items-center mb-3">
                        <input type="text" id="dr-summary" class="form-control mr-2" style="max-width: 280px"
                            placeholder="Filter tanggal">
                        <button class="btn btn-success btn-sm mr-2" id="exportSummary">Export Excel</button>
                        <button class="btn btn-secondary btn-sm" id="resetSummary">Reset</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="summaryTable">
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>Nama</th>
                                    <th>Opening</th>
                                    <th>Masuk</th>
                                    <th>Keluar</th>
                                    <th>Penyesuaian</th>
                                    <th>Closing</th>
                                    <th>Stok Saat Ini</th>
                                    <th>Mismatch?</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                {{-- HISTORY STOK --}}
                <div class="tab-pane fade" id="stock-history" role="tabpanel">
                    <div class="d-flex align-items-center mb-3">
                        <input type="text" id="dr-history" class="form-control mr-2" style="max-width: 280px"
                            placeholder="Filter tanggal">
                        <input type="text" id="searchHistory" class="form-control mr-2" style="max-width: 260px"
                            placeholder="Cari produk / SKU">
                        <button class="btn btn-success btn-sm mr-2" id="exportHistory">Export Excel</button>
                        <button class="btn btn-secondary btn-sm" id="resetHistory">Reset</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="historyTable">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Produk</th>
                                    <th>SKU</th>
                                    <th>User</th>
                                    <th>Perubahan</th>
                                    <th>Tipe</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <div id="historyPagination" class="mt-3"></div>
                    </div>
                </div>

                {{-- TRANSAKSI --}}
                <div class="tab-pane fade" id="transactions" role="tabpanel">
                    <div class="d-flex align-items-center mb-3">
                        <input type="text" id="dr-trans" class="form-control mr-2" style="max-width: 280px"
                            placeholder="Filter tanggal">
                        <input type="text" id="searchTrans" class="form-control mr-2" style="max-width: 260px"
                            placeholder="Cari user / tipe">
                        <button class="btn btn-success btn-sm mr-2" id="exportTrans">Export Excel</button>
                        <button class="btn btn-secondary btn-sm" id="resetTrans">Reset</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="transTable">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>User</th>
                                    <th>Tipe</th>
                                    <th>Total Harga</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <div id="transPagination" class="mt-3"></div>
                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script>
        $(function() {
            // ====== STATE ======
            let sumStart = null,
                sumEnd = null;
            let hisStart = null,
                hisEnd = null,
                hisSearch = '',
                hisPage = 1,
                hisPerPage = 10,
                hisData = [];
            let trStart = null,
                trEnd = null,
                trSearch = '',
                trPage = 1,
                trPerPage = 10,
                trData = [];
            let debounceTimer;

            // ====== DATE PICKERS ======
            $('#dr-summary, #dr-history, #dr-trans').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD',
                    applyLabel: 'Terapkan',
                    cancelLabel: 'Batal'
                }
            }).on('apply.daterangepicker', function(ev, p) {
                $(this).val(p.startDate.format('YYYY-MM-DD') + ' - ' + p.endDate.format('YYYY-MM-DD'))
                    .trigger('change');
            }).on('cancel.daterangepicker', function() {
                $(this).val('');
                $(this).trigger('change');
            });

            // ====== FETCHERS ======
            function fetchSummary() {
                let params = {};
                if (sumStart && sumEnd) {
                    params.start_date = sumStart;
                    params.end_date = sumEnd;
                }
                $.get("{{ route('reports.stock_summary') }}", params, function(res) {
                    let rows = '';
                    res.data.forEach(r => {
                        rows += `<tr>
                    <td>${r.sku}</td>
                    <td>${r.name}</td>
                    <td>${r.opening}</td>
                    <td>${r.in}</td>
                    <td>${r.out}</td>
                    <td>${r.adjustment}</td>
                    <td>${r.closing}</td>
                    <td>${r.current}</td>
                    <td>${r.mismatch ? '<span class="badge badge-danger">Ya</span>' : '<span class="badge badge-success">Tidak</span>'}</td>
                </tr>`;
                    });
                    $('#summaryTable tbody').html(rows);
                });
            }

            function fetchHistory() {
                let params = {};
                if (hisStart && hisEnd) {
                    params.start_date = hisStart;
                    params.end_date = hisEnd;
                }
                if (hisSearch) {
                    params.search = hisSearch;
                }
                $.get("{{ route('reports.stock_history') }}", params, function(res) {
                    hisData = res;
                    hisPage = 1;
                    renderHistory();
                });
            }

            function renderHistory() {
                let start = (hisPage - 1) * hisPerPage;
                let pageData = hisData.slice(start, start + hisPerPage);
                let rows = '';
                pageData.forEach(h => {
                    rows += `<tr>
                <td>${new Date(h.created_at).toLocaleString('id-ID',{timeZone:'Asia/Jakarta'})}</td>
                <td>${h.product?.name ?? '-'}</td>
                <td>${h.product?.sku ?? '-'}</td>
                <td>${h.user?.name ?? '-'}</td>
                <td>${h.quantity_change}</td>
                <td>${h.type}</td>
                <td>${h.note ?? '-'}</td>
            </tr>`;
                });
                $('#historyTable tbody').html(rows);
                renderPagination('#historyPagination', hisData.length, hisPage, hisPerPage, (p) => {
                    hisPage = p;
                    renderHistory();
                });
            }

            function fetchTrans() {
                let params = {};
                if (trStart && trEnd) {
                    params.start_date = trStart;
                    params.end_date = trEnd;
                }
                if (trSearch) {
                    params.search = trSearch;
                }
                $.get("{{ route('reports.transactions') }}", params, function(res) {
                    trData = res;
                    trPage = 1;
                    renderTrans();
                });
            }

            function renderTrans() {
                let start = (trPage - 1) * trPerPage;
                let pageData = trData.slice(start, start + trPerPage);
                let rows = '';
                pageData.forEach(t => {
                    rows += `<tr>
                <td>${new Date(t.created_at).toLocaleString('id-ID',{timeZone:'Asia/Jakarta'})}</td>
                <td>${t.user?.name ?? '-'}</td>
                <td>${t.type}</td>
                <td>${Number(t.total_price).toLocaleString('id-ID',{style:'currency',currency:'IDR'})}</td>
            </tr>`;
                });
                $('#transTable tbody').html(rows);
                renderPagination('#transPagination', trData.length, trPage, trPerPage, (p) => {
                    trPage = p;
                    renderTrans();
                });
            }

            function renderPagination(container, total, page, perPage, onClick) {
                const totalPages = Math.max(1, Math.ceil(total / perPage));
                let html = '<nav><ul class="pagination justify-content-center mb-0">';
                html +=
                    `<li class="page-item ${page<=1?'disabled':''}"><a class="page-link" href="#" data-p="${page-1}">&laquo;</a></li>`;
                for (let i = 1; i <= totalPages; i++) {
                    html +=
                        `<li class="page-item ${i===page?'active':''}"><a class="page-link" href="#" data-p="${i}">${i}</a></li>`;
                }
                html +=
                    `<li class="page-item ${page>=totalPages?'disabled':''}"><a class="page-link" href="#" data-p="${page+1}">&raquo;</a></li>`;
                html += '</ul></nav>';
                $(container).html(html);
                $(container + ' .page-link').off('click').on('click', function(e) {
                    e.preventDefault();
                    const p = parseInt($(this).data('p'));
                    if (!isNaN(p) && p >= 1 && p <= totalPages) onClick(p);
                });
            }

            // ====== BINDINGS ======

            // Summary date change
            $('#dr-summary').on('change', function() {
                const val = $(this).val();
                if (val) {
                    const [s, e] = val.split(' - ');
                    sumStart = s;
                    sumEnd = e;
                } else {
                    sumStart = null;
                    sumEnd = null;
                }
                fetchSummary();
            });
            $('#resetSummary').click(function() {
                $('#dr-summary').val('');
                sumStart = sumEnd = null;
                fetchSummary();
            });
            $('#exportSummary').click(function() {
                let url = "{{ route('reports.export.stock_summary') }}";
                if (sumStart && sumEnd) {
                    url += `?start_date=${sumStart}&end_date=${sumEnd}`;
                }
                window.location = url;
            });

            // History search & date
            $('#dr-history').on('change', function() {
                const val = $(this).val();
                if (val) {
                    const [s, e] = val.split(' - ');
                    hisStart = s;
                    hisEnd = e;
                } else {
                    hisStart = hisEnd = null;
                }
                fetchHistory();
            });
            $('#searchHistory').on('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    hisSearch = $(this).val();
                    fetchHistory();
                }, 350);
            });
            $('#resetHistory').click(function() {
                $('#dr-history').val('');
                $('#searchHistory').val('');
                hisStart = hisEnd = null;
                hisSearch = '';
                hisPage = 1;
                fetchHistory();
            });
            $('#exportHistory').click(function() {
                let params = [];
                if (hisStart && hisEnd) {
                    params.push(`start_date=${hisStart}`, `end_date=${hisEnd}`);
                }
                if (hisSearch) {
                    params.push(`search=${encodeURIComponent(hisSearch)}`);
                }
                let url = "{{ route('reports.export.stock_history') }}" + (params.length ?
                    `?${params.join('&')}` : '');
                window.location = url;
            });

            // Transactions search & date
            $('#dr-trans').on('change', function() {
                const val = $(this).val();
                if (val) {
                    const [s, e] = val.split(' - ');
                    trStart = s;
                    trEnd = e;
                } else {
                    trStart = trEnd = null;
                }
                fetchTrans();
            });
            $('#searchTrans').on('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    trSearch = $(this).val();
                    fetchTrans();
                }, 350);
            });
            $('#resetTrans').click(function() {
                $('#dr-trans').val('');
                $('#searchTrans').val('');
                trStart = trEnd = null;
                trSearch = '';
                trPage = 1;
                fetchTrans();
            });
            $('#exportTrans').click(function() {
                let params = [];
                if (trStart && trEnd) {
                    params.push(`start_date=${trStart}`, `end_date=${trEnd}`);
                }
                if (trSearch) {
                    params.push(`search=${encodeURIComponent(trSearch)}`);
                }
                let url = "{{ route('reports.export.transactions') }}" + (params.length ?
                    `?${params.join('&')}` : '');
                window.location = url;
            });

            // Initial load
            fetchSummary();
            fetchHistory();
            fetchTrans();
        });
    </script>
@endpush
