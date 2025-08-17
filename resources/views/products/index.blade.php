@extends('template.app')

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Produk</h6>
        </div>
        <div class="card-body">

            <!-- Search & Filter -->
            <div class="d-flex mb-3">
                <input type="text" id="searchInput" class="form-control mr-2" placeholder="Cari produk...">

                <input type="text" id="daterange" class="form-control mr-2" placeholder="Filter tanggal">

                <button class="btn btn-primary" id="filterBtn">Filter</button>
                <button class="btn btn-secondary ml-2" id="resetBtn">Reset</button>
            </div>

            <button class="btn btn-primary mb-3" id="addProductBtn">Tambah Produk</button>

            <table class="table table-bordered" id="productsTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>SKU</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Safety Stock</th>
                        <th>Tanggal Dibuat</th>
                        <th>Tanggal Update</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <!-- Pagination -->
            <nav>
                <ul class="pagination" id="pagination"></ul>
            </nav>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="productModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Produk</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="productId">
                    <div class="form-group">
                        <label for="productName">Nama</label>
                        <input type="text" id="productName" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="productCategory">Kategori</label>
                        <select id="productCategory" class="form-control">
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="productSku">SKU</label>
                        <input type="text" id="productSku" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="productPrice">Harga</label>
                        <input type="number" id="productPrice" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="productStock">Stok</label>
                        <input type="number" id="productStock" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="productSafetyStock">Safety Stock</label>
                        <input type="number" id="productSafetyStock" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="productImage">Gambar</label>
                        <input type="file" id="productImage" class="form-control">
                        <img id="previewImage" src="" class="img-fluid mt-2"
                            style="max-height:100px; display:none;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="saveProductBtn">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <script>
        $(document).ready(function() {
            let startDate = '';
            let endDate = '';
            let search = '';
            let currentPage = 1;

            // Init DateRange Picker
            $('#daterange').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD',
                    cancelLabel: 'Clear'
                }
            });

            $('#daterange').on('apply.daterangepicker', function(ev, picker) {
                startDate = picker.startDate.format('YYYY-MM-DD');
                endDate = picker.endDate.format('YYYY-MM-DD');
                $(this).val(startDate + ' - ' + endDate);
            });

            $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                startDate = '';
                endDate = '';
            });

            function formatRupiah(angka) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR'
                }).format(angka);
            }

            function formatTanggal(tgl) {
                return new Date(tgl).toLocaleString('id-ID', {
                    timeZone: 'Asia/Jakarta'
                });
            }

            function renderPagination(meta) {
                let pagination = '';
                if (meta.last_page > 1) {
                    for (let i = 1; i <= meta.last_page; i++) {
                        pagination += `<li class="page-item ${i==meta.current_page?'active':''}">
                            <a class="page-link pageBtn" href="#" data-page="${i}">${i}</a>
                        </li>`;
                    }
                }
                $('#pagination').html(pagination);
            }

            function fetchProducts(page = 1) {
                $.get("{{ route('products.list') }}", {
                    search: search,
                    start_date: startDate,
                    end_date: endDate,
                    page: page
                }, function(res) {
                    let rows = '';
                    res.data.forEach(function(product, index) {
                        let imgTag = product.image ?
                            `<img src="/storage/${product.image}" style="max-height:50px;">` : '';
                        rows += `<tr>
                    <td>${(res.from + index)}</td>
                    <td>${product.name} ${imgTag}</td>
                    <td>${product.category ? product.category.name : '-'}</td>
                    <td>${product.sku}</td>
                    <td>${formatRupiah(product.price)}</td>
                    <td>${product.stock}</td>
                    <td>${product.safety_stock}</td>
                    <td>${formatTanggal(product.created_at)}</td>
                    <td>${formatTanggal(product.updated_at)}</td>
                    <td>
                        <button class="btn btn-sm btn-info editBtn"
                            data-id="${product.id}"
                            data-name="${product.name}"
                            data-category="${product.category_id}"
                            data-sku="${product.sku}"
                            data-price="${product.price}"
                            data-stock="${product.stock}"
                            data-safety_stock="${product.safety_stock}"
                            data-image="${product.image}">Edit</button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="${product.id}">Hapus</button>
                    </td>
                </tr>`;
                    });
                    $('#productsTable tbody').html(rows);
                    renderPagination(res);
                });
            }

            // Event filter
            $('#filterBtn').click(function() {
                search = $('#searchInput').val();
                fetchProducts(1);
            });

            // Reset filter
            $('#resetBtn').click(function() {
                $('#searchInput').val('');
                $('#daterange').val('');
                startDate = '';
                endDate = '';
                search = '';
                fetchProducts(1);
            });

            // Pagination click
            $(document).on('click', '.pageBtn', function(e) {
                e.preventDefault();
                let page = $(this).data('page');
                fetchProducts(page);
            });

            // Load awal
            fetchProducts();

            // Add Produk
            $('#addProductBtn').click(function() {
                $('#productId').val('');
                $('#productName').val('');
                $('#productCategory').val('');
                $('#productSku').val('');
                $('#productPrice').val('');
                $('#productStock').val('');
                $('#productSafetyStock').val('');
                $('#productImage').val('');
                $('#previewImage').hide();
                $('#productModal').modal('show');
            });

            // Preview gambar
            $('#productImage').change(function() {
                if (this.files && this.files[0]) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        $('#previewImage').attr('src', e.target.result).show();
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });

            // Save Produk
            $('#saveProductBtn').click(function() {
                let id = $('#productId').val();
                let formData = new FormData();
                formData.append('name', $('#productName').val());
                formData.append('category_id', $('#productCategory').val());
                formData.append('sku', $('#productSku').val());
                formData.append('price', $('#productPrice').val());
                formData.append('stock', $('#productStock').val());
                formData.append('safety_stock', $('#productSafetyStock').val());
                if ($('#productImage')[0].files[0]) {
                    formData.append('image', $('#productImage')[0].files[0]);
                }
                formData.append('_token', "{{ csrf_token() }}");

                let url = id ? `/products/${id}` : "{{ route('products.store') }}";
                let method = id ? 'POST' : 'POST';
                if (id) formData.append('_method', 'PUT');

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function() {
                        $('#productModal').modal('hide');
                        fetchProducts(currentPage);
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data produk berhasil disimpan'
                        });
                    },
                    error: function(xhr) {
                        let msg = '';
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            msg += value + '<br>';
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: msg
                        });
                    }
                });
            });

            // Edit Produk
            $(document).on('click', '.editBtn', function() {
                $('#productId').val($(this).data('id'));
                $('#productName').val($(this).data('name'));
                $('#productCategory').val($(this).data('category'));
                $('#productSku').val($(this).data('sku'));
                $('#productPrice').val($(this).data('price'));
                $('#productStock').val($(this).data('stock'));
                $('#productSafetyStock').val($(this).data('safety_stock'));
                if ($(this).data('image')) {
                    $('#previewImage').attr('src', '/storage/' + $(this).data('image')).show();
                } else {
                    $('#previewImage').hide();
                }
                $('#productModal').modal('show');
            });

            // Hapus Produk
            $(document).on('click', '.deleteBtn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Apakah anda yakin?',
                    text: "Data produk akan dihapus!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/products/${id}`,
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: "{{ csrf_token() }}"
                            },
                            success: function() {
                                fetchProducts(currentPage);
                                Swal.fire(
                                    'Terhapus!',
                                    'Data produk berhasil dihapus',
                                    'success'
                                )
                            }
                        });
                    }
                })
            });
        });
    </script>
@endpush
