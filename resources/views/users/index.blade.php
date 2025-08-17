@extends('template.app')

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Pengguna</h6>
        </div>
        <div class="card-body">
            <button class="btn btn-primary mb-3" id="addUserBtn">Tambah Pengguna</button>

            <div class="mb-3">
                <input type="text" id="searchInput" class="form-control" placeholder="Cari nama atau email...">
            </div>

            <table class="table table-bordered" id="usersTable">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Dibuat</th>
                        <th>Update</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="userModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pengguna</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="userId">
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" id="userName" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="userEmail" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" id="userPassword" class="form-control">
                        <small class="text-muted">Kosongkan jika tidak ingin mengganti password</small>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select id="userRole" class="form-control">
                            <option value="admin">Admin</option>
                            <option value="karyawan">Karyawan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" id="saveUserBtn">Simpan</button>
                    <button class="btn btn-secondary" data-dismiss="modal">Batal</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            fetchUsers();

            function formatTanggal(tgl) {
                return new Date(tgl).toLocaleString('id-ID', {
                    timeZone: 'Asia/Jakarta'
                });
            }

            function fetchUsers() {
                let query = $('#searchInput').val();
                let url = "{{ route('users.list') }}";
                if (query) url += `?q=${encodeURIComponent(query)}`;

                $.get(url, function(data) {
                    let rows = '';
                    data.forEach(function(user, index) {
                        rows += `<tr>
                    <td>${index+1}</td>
                    <td>${user.name}</td>
                    <td>${user.email}</td>
                    <td>${user.role}</td>
                    <td>${formatTanggal(user.created_at)}</td>
                    <td>${formatTanggal(user.updated_at)}</td>
                    <td>
                        <button class="btn btn-sm btn-info editBtn" 
                            data-id="${user.id}" 
                            data-name="${user.name}" 
                            data-email="${user.email}" 
                            data-role="${user.role}">Edit</button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="${user.id}">Hapus</button>
                    </td>
                </tr>`;
                    });
                    $('#usersTable tbody').html(rows);
                });
            }

            $('#searchInput').on('input', function() {
                fetchUsers();
            });

            $('#addUserBtn').click(function() {
                $('#userId').val('');
                $('#userName').val('');
                $('#userEmail').val('');
                $('#userPassword').val('');
                $('#userRole').val('karyawan');
                $('#userModal').modal('show');
            });

            $('#saveUserBtn').click(function() {
                let id = $('#userId').val();
                let data = {
                    name: $('#userName').val(),
                    email: $('#userEmail').val(),
                    password: $('#userPassword').val(),
                    role: $('#userRole').val(),
                    _token: "{{ csrf_token() }}"
                };
                let url = id ? `/users/${id}` : "{{ route('users.store') }}";
                let method = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: data,
                    success: function() {
                        $('#userModal').modal('hide');
                        fetchUsers();
                        Swal.fire('Berhasil', 'Data berhasil disimpan', 'success');
                    },
                    error: function(xhr) {
                        let msg = '';
                        $.each(xhr.responseJSON.errors, function(k, v) {
                            msg += v + '<br>';
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: msg
                        });
                    }
                });
            });

            $(document).on('click', '.editBtn', function() {
                $('#userId').val($(this).data('id'));
                $('#userName').val($(this).data('name'));
                $('#userEmail').val($(this).data('email'));
                $('#userPassword').val('');
                $('#userRole').val($(this).data('role'));
                $('#userModal').modal('show');
            });

            $(document).on('click', '.deleteBtn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Apakah yakin?',
                    text: 'Data pengguna akan dihapus!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/users/${id}`,
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: "{{ csrf_token() }}"
                            },
                            success: function() {
                                fetchUsers();
                                Swal.fire('Terhapus', 'Data berhasil dihapus',
                                    'success');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
