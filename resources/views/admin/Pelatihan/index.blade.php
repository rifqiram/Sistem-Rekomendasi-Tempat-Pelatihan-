@extends('layouts.admin')

@section('title', 'Manajemen Pelatihan')
@section('page_title', 'Data Pelatihan')

@section('breadcrumb')
    <li class="breadcrumb-item active">Pelatihan</li>
@endsection

@push('styles')
<style>
    /* Admin Table Modernization */
    .card-modern {
        border: none;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border-radius: 0.75rem;
    }

    .card-modern .card-header {
        background-color: transparent;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        padding: 1.25rem 1.5rem;
    }

    .table-modern thead th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e2e8f0;
        padding: 1rem 1.5rem;
    }

    .table-modern tbody td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Minimalist Action Buttons */
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .btn-icon.edit { color: #3b82f6; background-color: rgba(59, 130, 246, 0.1); border: none; }
    .btn-icon.edit:hover { background-color: #3b82f6; color: white; }

    .btn-icon.delete { color: #ef4444; background-color: rgba(239, 68, 68, 0.1); border: none; }
    .btn-icon.delete:hover { background-color: #ef4444; color: white; }

    /* Form Modernization */
    .modal-content {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .form-label {
        
        font-size: 0.85rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.4rem;
    }

    .form-control, .form-select {
        border-color: #cbd5e1;
        padding: 0.6rem 1rem;
        border-radius: 0.5rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-modern mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title fw-bold m-0 text-dark me-auto"><i class="fas fa-book-open text-muted me-2"></i> Daftar Pelatihan Teknis</h3>
                <button type="button" class="btn btn-primary btn-sm px-3 fw-semibold rounded-pill shadow-sm" onclick="showModal('create')">
                    <i class="fas fa-plus me-1"></i> Tambah Pelatihan
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-modern mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Judul Pelatihan</th>
                                <th>Lembaga (TC)</th>
                                <th>Bidang / Metode</th>
                                <th>Skill Min.</th>
                                <th>Status</th>
                                <th style="width: 120px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="pelatihan-table-body">
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                                    <div class="small">Memuat data pelatihan...</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal CRUD -->
<div class="modal fade" id="pelatihanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <form id="pelatihanForm" class="modal-content">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="pelatihanModalLabel">Tambah Pelatihan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                    <input type="hidden" id="pelatihan_id">

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Judul Pelatihan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light" id="judul" required placeholder="Contoh: Bootcamp Fullstack Web Developer">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lembaga / Training Center <span class="text-danger">*</span></label>
                            <select class="form-select" id="training_center_id" required>
                                <option value="" disabled selected>Pilih Lembaga penyelenggara...</option>
                                <!-- Loaded via JS -->
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Deskripsi Singkat</label>
                        <textarea class="form-control" id="deskripsi" rows="2" placeholder="Tuliskan penjelasan singkat pelatihan..."></textarea>
                    </div>

                    <!-- Recommendation Engine Attributes -->
                    <div class="p-3 mb-4 rounded-3 border border-primary border-opacity-25" style="background-color: rgba(79, 70, 229, 0.05);">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-magic text-primary me-2"></i>
                            <strong class="text-primary small text-uppercase letter-spacing-1">Atribut Recommendation Engine</strong>
                        </div>
                        <p class="text-muted small mb-3 lh-sm">Data di bawah ini menjadi variabel utama penentu skor kecocokan pelatihan ini dengan preferensi pencari kerja.</p>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Kategori Bidang</label>
                                <select class="form-select form-select-sm" id="interest_category">
                                    <option value="">Tidak Ditentukan</option>
                                    <option value="IT">IT dan Teknologi Komputer</option>
                                    <option value="Bisnis">Bisnis dan Manajemen</option>
                                    <option value="Desain">Desain dan Kreatif</option>
                                    <option value="Bahasa">Bahasa Asing</option>
                                    <option value="Busana">Tata Busana</option>
                                    <option value="Memasak">Tata Boga</option>
                                    <option value="Pertanian">Pertanian</option>
                                    <option value="Peternakan">Peternakan</option>
                                    <option value="Pengelasan">Pengelasan</option>
                                    <option value="Caretaker">Caretaker</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Metode</label>
                                <select class="form-select form-select-sm" id="method">
                                    <option value="Online">Daring (Online)</option>
                                    <option value="Offline">Tatap Muka (Offline)</option>
                                    <option value="Hybrid">Campuran (Hybrid)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Syarat Skill Minimum</label>
                                <select class="form-select form-select-sm" id="required_skill">
                                    <option value="Beginner">Pemula (Beginner)</option>
                                    <option value="Intermediate">Menengah (Intermediate)</option>
                                    <option value="Advanced">Mahir (Advanced)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Skor Popularitas</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control bg-light" id="popularity_display" readonly value="0 pengguna">
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 mt-4 text-dark" style="font-size: 0.9rem;">Informasi Tambahan Pelaksanaan</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_mulai" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_selesai" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sertifikat Kelulusan</label>
                            <input type="text" class="form-control" id="sertifikat" placeholder="Cth: BNSP / Lembaga">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status Visibilitas</label>
                            <select class="form-select bg-light" id="is_active">
                                <option value="1">Aktif (Tampil Publik)</option>
                                <option value="0">Draft (Sembunyikan)</option>
                            </select>
                        </div>
                    </div>

                </div>
            <div class="modal-footer border-top-0 px-4 pb-4">
                <button type="button" class="btn btn-light fw-medium" data-bs-dismiss="modal">Batalkan</button>
                <button type="submit" class="btn btn-primary fw-bold px-4" id="btnSave">Simpan Pelatihan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let pelatihanModal;
    let trainingCentersList = [];

    document.addEventListener('DOMContentLoaded', async () => {
        pelatihanModal = new bootstrap.Modal(document.getElementById('pelatihanModal'));
        await fetchTrainingCenters();
        loadData();
    });

    async function fetchTrainingCenters() {
        try {
            const rawRes = await window.authFetch(`${window.apiBase}/training-centers`).then(window.parseApi);
            trainingCentersList = Array.isArray(rawRes) ? rawRes : (rawRes.data || []);

            const selectTc = document.getElementById('training_center_id');
            trainingCentersList.forEach(tc => {
                const opt = document.createElement('option');
                opt.value = tc.id;
                opt.textContent = tc.nama;
                selectTc.appendChild(opt);
            });
        } catch (e) {
            console.error("Gagal load TC list", e);
        }
    }

    async function loadData() {
        const tbody = document.getElementById('pelatihan-table-body');
        try {
            const rawRes = await window.authFetch(`${window.apiBase}/trainings`).then(window.parseApi);
            const dataArray = Array.isArray(rawRes) ? rawRes : (rawRes.data || []);

            tbody.innerHTML = '';

            if (dataArray.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-folder-open fs-2 mb-3 opacity-25 d-block"></i>Belum ada data Pelatihan</td></tr>`;
                return;
            }

            dataArray.forEach((item, index) => {
                const tr = document.createElement('tr');

                // Modern Badge for Status
                const badgeActive = item.is_active
                    ? '<span class="badge rounded-pill" style="background-color: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16,185,129,0.2);"><i class="fas fa-circle me-1" style="font-size:8px;"></i> Aktif Publik</span>'
                    : '<span class="badge rounded-pill" style="background-color: rgba(100, 116, 139, 0.1); color: #64748b; border: 1px solid rgba(100,116,139,0.2);"><i class="fas fa-circle me-1" style="font-size:8px;"></i> Draft</span>';

                // Skill Badge
                let skillBadgeStyle = '';
                if(item.required_skill === 'Advanced') skillBadgeStyle = 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25';
                else if(item.required_skill === 'Intermediate') skillBadgeStyle = 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25';
                else skillBadgeStyle = 'bg-success bg-opacity-10 text-success border border-success border-opacity-25';

                let tcName = '<i class="text-danger small"><i class="fas fa-exclamation-triangle"></i> TC Terhapus</i>';
                const tcMatch = trainingCentersList.find(tc => tc.id == item.training_center_id);
                if (tcMatch) tcName = tcMatch.nama;
                if (item.training_center) tcName = item.training_center.nama;

                tr.innerHTML = `
                    <td class="text-muted">${index + 1}</td>
                    <td class="fw-bold text-dark">${item.judul}</td>
                    <td><i class="fas fa-building text-muted small me-1"></i> ${tcName}</td>
                    <td>
                        <div class="fw-medium text-dark">${item.interest_category || '-'}</div>
                        <div class="text-muted small"><i class="fas fa-chalkboard-teacher opacity-50 me-1"></i> ${item.method || '-'}</div>
                    </td>
                    <td><span class="badge rounded-pill ${skillBadgeStyle}">${item.required_skill || '-'}</span></td>
                    <td>${badgeActive}</td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                            <button class="btn-icon edit" onclick='editData(${JSON.stringify(item).replace(/'/g, "\\'")})' title="Edit Pelatihan">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button class="btn-icon delete" onclick="confirmDelete(${item.id}, '${item.judul}')" title="Hapus Pelatihan">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } catch (error) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4"><i class="fas fa-exclamation-circle me-1"></i> Gagal memuat data</td></tr>`;
            console.error("Load data error: ", error);
        }
    }

    function showModal(mode) {
        document.getElementById('pelatihanForm').reset();

        const btnSave = document.getElementById('btnSave');
        if (btnSave) {
            btnSave.disabled = false;
            btnSave.innerHTML = 'Simpan Pelatihan';
        }

        if (mode === 'create') {
            document.getElementById('pelatihanModalLabel').textContent = 'Tambah Pelatihan Baru';
            document.getElementById('pelatihan_id').value = '';
            document.getElementById('is_active').value = '1';
            document.getElementById('popularity_display').value = '0 pengguna';
        }
        pelatihanModal.show();
    }

    function editData(item) {
        showModal('edit');
        document.getElementById('pelatihanModalLabel').textContent = 'Edit Data Pelatihan';

        document.getElementById('pelatihan_id').value = item.id;
        document.getElementById('judul').value = item.judul;
        document.getElementById('training_center_id').value = item.training_center_id || '';
        document.getElementById('deskripsi').value = item.deskripsi || '';
        document.getElementById('interest_category').value = item.interest_category || '';
        document.getElementById('method').value = item.method || 'Online';
        document.getElementById('required_skill').value = item.required_skill || 'Beginner';
        document.getElementById('popularity_display').value = (item.approved_enrollments_count || 0) + " pengguna";
        document.getElementById('tanggal_mulai').value = item.tanggal_mulai ? item.tanggal_mulai.split('T')[0] : '';
        document.getElementById('tanggal_selesai').value = item.tanggal_selesai ? item.tanggal_selesai.split('T')[0] : '';
        document.getElementById('sertifikat').value = item.sertifikat || '';
        document.getElementById('is_active').value = item.is_active ? '1' : '0';
    }

    document.getElementById('pelatihanForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const id = document.getElementById('pelatihan_id').value;
        const btnSave = document.getElementById('btnSave');

        const payload = {
            judul: document.getElementById('judul').value,
            training_center_id: document.getElementById('training_center_id').value,
            deskripsi: document.getElementById('deskripsi').value,
            interest_category: document.getElementById('interest_category').value,
            method: document.getElementById('method').value,
            required_skill: document.getElementById('required_skill').value,
            tanggal_mulai: document.getElementById('tanggal_mulai').value,
            tanggal_selesai: document.getElementById('tanggal_selesai').value,
            sertifikat: document.getElementById('sertifikat').value,
            is_active: document.getElementById('is_active').value === '1',
        };

        const url = id ? `${window.apiBase}/trainings/${id}` : `${window.apiBase}/trainings`;
        const methodHttp = id ? 'PUT' : 'POST';

        try {
            const originalText = btnSave.innerHTML;
            btnSave.disabled = true;
            btnSave.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';

            const response = await window.authFetch(url, {
                method: methodHttp,
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                const resData = await response.json();
                let errMsg = resData.message || 'Gagal menyimpan data';
                if(resData.errors) {
                    const firstKey = Object.keys(resData.errors)[0];
                    errMsg = resData.errors[firstKey][0];
                }
                throw new Error(errMsg);
            }

            pelatihanModal.hide();
            window.showToast('Data pelatihan berhasil disimpan!', 'success');
            loadData();
            btnSave.disabled = false;
            btnSave.innerHTML = originalText;
        } catch (error) {
            window.showError('Gagal!', error.message);
            btnSave.disabled = false;
            btnSave.innerHTML = 'Simpan Pelatihan';
        }
    });

    function confirmDelete(id, judul) {
        window.confirmAction(
            'Hapus Pelatihan?',
            `Apakah Anda yakin ingin menghapus pelatihan "${judul}" secara permanen?`,
            'Ya, Hapus',
            async () => {
                try {
                    const response = await window.authFetch(`${window.apiBase}/trainings/${id}`, {
                        method: 'DELETE'
                    });

                    if (!response.ok) {
                        const resData = await response.json();
                        throw new Error(resData.message || 'Gagal menghapus');
                    }

                    window.showToast('Pelatihan berhasil dihapus', 'success');
                    loadData();
                } catch (error) {
                    window.showError('Gagal', error.message);
                }
            }
        );
    }
</script>
@endpush