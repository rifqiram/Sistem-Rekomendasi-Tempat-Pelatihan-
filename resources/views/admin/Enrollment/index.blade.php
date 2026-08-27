@extends('layouts.admin')

@section('title', 'Data Pendaftaran')
@section('page_title', 'Data Pendaftaran')

@section('breadcrumb')
    <li class="breadcrumb-item active">Pendaftaran</li>
@endsection

@push('styles')
<style>
    /* Action Buttons UI/UX Refinement */
    .action-btn-group {
        display: flex;
        justify-content: center;
        gap: 8px;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: flex-start;
        height: 34px;
        width: 34px;
        border-radius: 8px;
        background-color: #ffffff;
        padding: 0;
        margin: 0;
        border: 1px solid transparent;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .action-btn i {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: color 0.25s ease;
    }

    .action-btn .btn-text {
        white-space: nowrap;
        font-size: 13px;
        font-weight: 600;
        opacity: 0;
        width: 0;
        transform: translateX(-10px);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Approve Button */
    .action-btn.approve {
        border-color: var(--success-color, #198754);
        color: var(--success-color, #198754);
    }

    /* Reject Button */
    .action-btn.reject {
        border-color: var(--danger-color, #dc3545);
        color: var(--danger-color, #dc3545);
    }

    /* Desktop & Tablet Hover Effects */
    @media (min-width: 768px) {
        .action-btn:hover {
            width: auto;
            padding-right: 12px;
        }

        .action-btn:hover .btn-text {
            opacity: 1;
            width: auto;
            transform: translateX(0);
        }

        .action-btn.approve:hover {
            background-color: var(--success-color, #198754);
            color: #ffffff;
        }

        .action-btn.reject:hover {
            background-color: var(--danger-color, #dc3545);
            color: #ffffff;
        }
    }

    /* Ensure icon color changes on hover for desktop */
    @media (min-width: 768px) {
        .action-btn:hover i {
            color: #ffffff;
        }
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header border-0 pb-3 d-flex justify-content-between align-items-center">
                <h3 class="card-title fw-bold m-0 text-dark"><i class="fas fa-clipboard-list text-muted me-2"></i> Riwayat Pendaftaran User</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle text-center">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Tanggal Daftar</th>
                                <th>Nama Peserta</th>
                                <th>Pelatihan</th>
                                <th>Training Center</th>
                                <th>Status</th>
                                <th style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="enrollment-table-body">
                            <tr>
                                <td colspan="7" class="py-4">
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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        loadData();
    });

    async function loadData() {
        const tbody = document.getElementById('enrollment-table-body');

        try {
            const res = await window.authFetch(window.apiBase + '/admin/enrollments');
            const payload = await res.json();
            const data = payload.data?.data ?? payload.data;

            tbody.innerHTML = '';

            if (!data || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-muted py-4">Belum ada pendaftaran</td></tr>`;
                return;
            }

            data.forEach((item, index) => {
                const tr = createRow(item, index);
                tbody.insertAdjacentHTML('beforeend', tr);
            });

        } catch (error) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-danger py-4">Gagal memuat data pendaftaran.</td></tr>`;
        }
    }

    function createRow(item, index) {
        const user = item.user ? item.user.name : '-';
        const pel = item.pelatihan ? item.pelatihan.judul : '-';
        const tc = item.training_center ? item.training_center.nama : '-';
        const date = new Date(item.tanggal_daftar || item.created_at).toLocaleDateString('id-ID');

        // Note: For now the DB might still return 'terdaftar' as status.
        // We map it to 'pending' to match the new UI standard until backend is updated.
        let currentStatus = item.status;
        if (currentStatus === 'terdaftar') {
            currentStatus = 'pending';
        }

        return `
            <tr>
                <td>${index + 1}</td>
                <td>${date}</td>
                <td class="text-start fw-semibold">${user}</td>
                <td class="text-start">${pel}</td>
                <td class="text-start">${tc}</td>
                <td>${renderStatus(currentStatus)}</td>
                <td>${renderAction(item.id, currentStatus)}</td>
            </tr>
        `;
    }

    function renderStatus(status) {
        if (status === 'pending') {
            return `<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Pending</span>`;
        } else if (status === 'approved') {
            return `<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Disetujui</span>`;
        } else if (status === 'rejected') {
            return `<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Ditolak</span>`;
        } else {
            return `<span class="badge bg-secondary">${status}</span>`;
        }
    }

    function renderAction(id, status) {
        if (status === 'pending') {
            return `
                <div class="action-btn-group">
                    <button class="action-btn approve" onclick="approveEnrollment(${id})" title="Setujui">
                        <i class="fas fa-check"></i>
                        <span class="btn-text">Setujui</span>
                    </button>
                    <button class="action-btn reject" onclick="rejectEnrollment(${id})" title="Tolak">
                        <i class="fas fa-times"></i>
                        <span class="btn-text">Tolak</span>
                    </button>
                </div>
            `;
        } else if (status === 'approved') {
            return `<span class="text-success fw-bold"><i class="fas fa-check"></i> Sudah Disetujui</span>`;
        } else if (status === 'rejected') {
            return `<span class="text-danger fw-bold"><i class="fas fa-times"></i> Sudah Ditolak</span>`;
        } else {
            return `-`;
        }
    }

    async function approveEnrollment(id) {
        const result = await window.showConfirm(
            'Setujui Pendaftaran',
            'Apakah Anda yakin ingin menyetujui pendaftaran ini?',
            'Ya, Setujui'
        );
        if(result.isConfirmed) {
            window.authFetch(window.apiBase + `/admin/enrollments/${id}/status`, {
                method: 'PATCH',
                body: JSON.stringify({ status: 'approved' })
            })
            .then(res => res.json())
            .then(data => {
                window.showToast('Pendaftaran disetujui', 'success');
                loadData();
            })
            .catch(err => {
                window.showToast('Gagal menyetujui pendaftaran', 'error');
            });
        }
    }

    async function rejectEnrollment(id) {
        const result = await window.showConfirm(
            'Tolak Pendaftaran',
            'Apakah Anda yakin ingin menolak pendaftaran ini?',
            'Ya, Tolak'
        );
        if(result.isConfirmed) {
            window.authFetch(window.apiBase + `/admin/enrollments/${id}/status`, {
                method: 'PATCH',
                body: JSON.stringify({ status: 'rejected' })
            })
            .then(res => res.json())
            .then(data => {
                window.showToast('Pendaftaran ditolak', 'success');
                loadData();
            })
            .catch(err => {
                window.showToast('Gagal menolak pendaftaran', 'error');
            });
        }
    }
</script>
@endpush