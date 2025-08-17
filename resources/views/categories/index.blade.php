@extends('template.app')

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Kategori</h6>
        </div>
        <div class="card-body">
            <button class="btn btn-primary mb-3" id="addCategoryBtn">Tambah Kategori</button>

            <div class="table-responsive">
                <table class="table table-bordered" id="categoriesTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Dibuat Pada</th>
                            <th>Diperbarui Pada</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Kategori</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="categoryId">
                    <div class="form-group">
                        <label for="categoryName">Nama</label>
                        <input type="text" id="categoryName" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="saveCategoryBtn">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            fetchCategories();

            function fetchCategories() {
                $.get("{{ route('categories.list') }}", function(data) {
                    let rows = '';
                    data.forEach(function(category, index) {
                        // Format tanggal menggunakan moment.js untuk client-side, bisa juga pakai server-side Carbon
                        let created = moment(category.created_at).tz('Asia/Jakarta').locale('id')
                            .format('DD MMMM YYYY HH:mm');
                        let updated = moment(category.updated_at).tz('Asia/Jakarta').locale('id')
                            .format('DD MMMM YYYY HH:mm');

                        rows += `<tr>
                    <td>${index + 1}</td>
                    <td>${category.name}</td>
                    <td>${created}</td>
                    <td>${updated}</td>
                    <td>
                        <button class="btn btn-sm btn-info editBtn" data-id="${category.id}" data-name="${category.name}">Ubah</button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="${category.id}">Hapus</button>
                    </td>
                </tr>`;
                    });
                    $('#categoriesTable tbody').html(rows);
                });
            }

            $('#addCategoryBtn').click(function() {
                $('#categoryId').val('');
                $('#categoryName').val('');
                $('#categoryModal').modal('show');
            });

            $('#saveCategoryBtn').click(function() {
                let id = $('#categoryId').val();
                let name = $('#categoryName').val();
                let url = id ? `/categories/${id}` : "{{ route('categories.store') }}";
                let method = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: {
                        name: name,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function() {
                        $('#categoryModal').modal('hide');
                        fetchCategories();
                    }
                });
            });

            $(document).on('click', '.editBtn', function() {
                $('#categoryId').val($(this).data('id'));
                $('#categoryName').val($(this).data('name'));
                $('#categoryModal').modal('show');
            });

            $(document).on('click', '.deleteBtn', function() {
                if (confirm("Apakah Anda yakin ingin menghapus kategori ini?")) {
                    let id = $(this).data('id');
                    $.ajax({
                        url: `/categories/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function() {
                            fetchCategories();
                        }
                    });
                }
            });
        });
    </script>

    <!-- Moment.js dan Moment Timezone -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.43/moment-timezone-with-data.min.js"></script>
@endpush
