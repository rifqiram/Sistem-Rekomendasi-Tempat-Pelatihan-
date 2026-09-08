@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')
@section('page_title', 'Data Pengguna')

@section('breadcrumb')
    <li class="breadcrumb-item active">Users</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header border-0 pb-3 d-flex justify-content-between align-items-center">
                <h3 class="card-title fw-bold m-0 text-dark"><i class="fas fa-users text-muted me-2"></i> Daftar Pengguna Sistem</h3>
            </div>
            <div class="card-body">
                <div id="alertBox" class="alert d-none"></div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Tgl Daftar</th>
                                <th>Status</th>
                                <th class="text-center" style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="user-table-body">
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <div class="mt-2">Memuat data...</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Toggle Status -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Status Akun</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <h4 id="statusUserName" class="fw-bold mb-3">Nama User</h4>
                <p>Apakah Anda yakin ingin mengubah status akun ini menjadi <strong id="statusTargetText">Aktif</strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnConfirmStatus">Ya, Ubah Status</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let statusModal;
    let currentUser = null;

    document.addEventListener('DOMContentLoaded', async () => {
        statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
        await loadData();
    });

    async function loadData() {
        const tbody = document.getElementById('user-table-body');
        try {
            const res = await window.authFetch(window.apiBase + '/admin/users');
            const payload = await res.json();
            const data = payload.data?.data ?? payload.data;

            tbody.innerHTML = '';

            if (!data || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">Belum ada user terdaftar.</td></tr>`;
                return;
            }

            data.forEach((item, index) => {
                const date = new Date(item.created_at).toLocaleDateString('id-ID');
                const badge = item.is_active
                    ? '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Aktif</span>'
                    : '<span class="badge bg-danger"><i class="fas fa-ban me-1"></i> Non-Aktif</span>';

                const toggleBtnClass = item.is_active ? 'btn-danger' : 'btn-success';
                const toggleBtnIcon = item.is_active ? 'fa-ban' : 'fa-check';
                const toggleBtnTitle = item.is_active ? 'Non-Aktifkan Akun' : 'Aktifkan Akun';

                const tr = `
                    <tr>
                        <td>${index + 1}</td>
                        <td class="fw-semibold">${item.name}</td>
                        <td>${item.email}</td>
                        <td>${date}</td>
                        <td>${badge}</td>
                        <td class="text-center">
                            <button class="btn btn-sm ${toggleBtnClass}" onclick='confirmToggleStatus(${JSON.stringify(item).replace(/'/g, "\\'")})' title="${toggleBtnTitle}">
                                <i class="fas ${toggleBtnIcon}"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', tr);
            });

        } catch (error) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">Gagal memuat data pengguna.</td></tr>`;
        }
    }

    function confirmToggleStatus(item) {
        currentUser = item;
        document.getElementById('statusUserName').textContent = item.name;

        const targetStatus = !item.is_active; // kebalikan
        document.getElementById('statusTargetText').textContent = targetStatus ? 'AKTIF (Bisa Login)' : 'NON-AKTIF (Tidak bisa masuk)';

        const btn = document.getElementById('btnConfirmStatus');
        btn.className = targetStatus ? 'btn btn-success' : 'btn btn-danger';

        statusModal.show();
    }

    document.getElementById('btnConfirmStatus').addEventListener('click', async function() {
        if (!currentUser) return;
        const btn = this;
        const alertBox = document.getElementById('alertBox');

        try {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            const targetStatus = !currentUser.is_active;

            const response = await window.authFetch(`${window.apiBase}/admin/users/${currentUser.id}/status`, {
                method: 'PATCH',
                body: JSON.stringify({ is_active: targetStatus })
            });

            if (!response.ok) {
                throw new Error('Gagal merubah status.');
            }

            statusModal.hide();
            await loadData();

            alertBox.className = 'alert alert-success mt-3';
            alertBox.innerHTML = '<i class="fas fa-check-circle"></i> Status pengguna berhasil diperbarui.';
            alertBox.classList.remove('d-none');
            setTimeout(() => alertBox.classList.add('d-none'), 3000);

        } catch (error) {
            alert('Error: ' + error.message);
        } finally {
            btn.disabled = false;
            btn.textContent = 'Ya, Ubah Status';
        }
    });
</script>
@endpush