@extends('layouts.user')

@section('title', 'Riwayat Pendaftaran')

@push('styles')
<style>
    /* Status Colors based on CSS Variables */
    :root {
        --status-active-bg: rgba(59, 130, 246, 0.1);
        --status-active-text: var(--info-color);
        --status-success-bg: rgba(16, 185, 129, 0.1);
        --status-success-text: var(--secondary-color);
        --status-danger-bg: rgba(239, 68, 68, 0.1);
        --status-danger-text: var(--danger-color);
    }

    .history-card {
        background-color: #ffffff;
        border: 1px solid var(--primary-color);
        border-radius: 18px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.25s ease;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }

    .history-card::before {
        display: none; /* Removed line */
    }

    .history-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    /* Info Area */
    .history-content {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .history-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
    }

    .history-title-area {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .history-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
        background-color: rgba(79, 70, 229, 0.08);
        color: var(--primary-color);
    }

    .history-title-area h5 {
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 0.25rem;
        font-size: 18px;
        line-height: 1.3;
    }

    .history-info-rows {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        padding-left: calc(48px + 1rem); /* Align with text */
    }

    .history-info-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-muted);
    }

    .history-info-row.tc-name {
        font-size: 15px;
        font-weight: 500;
    }

    .history-info-row.date-row {
        font-size: 14px;
        color: #6b7280;
    }

    .status-badge-modern {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 1rem;
        border-radius: 50rem;
        font-size: 0.85rem;
        font-weight: 600;
        white-space: nowrap;
    }

    /* Button Area */
    .history-action {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        padding-top: 0.5rem;
    }

    .btn-detail {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.5rem 1.25rem;
        background-color: #ffffff;
        color: var(--text-main);
        border: 1px solid var(--primary-color);
        border-radius: 50rem;
        font-size: 0.9rem;
        font-weight: 600;
        transition: all 0.2s ease;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-detail .arrow-icon {
        transition: transform 0.2s ease;
    }

    .btn-detail:hover {
        background-color: var(--primary-color);
        color: #ffffff;
    }

    .btn-detail:hover .arrow-icon {
        transform: translateX(4px);
    }

    @media (max-width: 767.98px) {
        .history-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .history-info-rows {
            padding-left: 0;
            margin-top: 0.5rem;
        }

        .btn-detail {
            width: 100%;
        }
    }

    .empty-state-icon {
        font-size: 4rem;
        color: var(--border-color);
        margin-bottom: 1rem;
    }

    /* Modal Customization */
    .modal-content {
        border: none;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
        overflow: hidden;
    }

    .modal-header-sticky {
        position: sticky;
        top: 0;
        z-index: 1020;
        background: var(--bg-color);
        transition: padding 0.3s ease, box-shadow 0.3s ease;
    }

    .modal-header-custom {
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.05), rgba(59, 130, 246, 0.05));
        padding: 2rem 1.5rem 1.5rem;
        border-bottom: 1px solid var(--border-color);
        position: relative;
    }

    .modal-close-btn {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        transition: all 0.2s;
        z-index: 1056; /* Harus lebih tinggi dari sticky header (1020) */
    }

    .modal-close-btn:hover {
        background: var(--bg-color);
        color: var(--danger-color);
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        {{-- Header Page --}}
        <div class="d-flex align-items-center mb-4 gap-3">
            <div class="bg-amber-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.25rem; background-color: rgba(245, 158, 11, 0.1); color: var(--warning-color);">
                <i class="fas fa-history"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0" style="color: var(--text-main);">Riwayat Pendaftaran</h3>
                <p class="text-muted mb-0 small">Lacak status program pelatihan yang telah Anda pilih.</p>
            </div>
        </div>

        <div id="loadingState" class="text-center py-5">
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem; border-width: 0.25em;" role="status"></div>
            <h5 class="fw-bold" style="color: var(--text-main);">Memuat Data</h5>
            <div class="text-muted small">Mengambil riwayat pendaftaran Anda...</div>
        </div>

        <div id="emptyState" class="text-center py-5 d-none">
            <div class="empty-state-icon" style="font-size: 5rem; margin-bottom: 1.5rem;">📄</div>
            <h4 class="fw-bold mb-2" style="color: var(--text-main);">Belum Ada Riwayat Pendaftaran</h4>
            <p class="text-muted mb-4 small mx-auto" style="max-width: 400px;">Anda belum mendaftar pada pelatihan apa pun.</p>
            <a href="{{ route('user.recommendations') }}" class="btn fw-bold px-4 py-2 rounded-pill shadow-sm" style="background-color: var(--primary-color); color: white;">
                <i class="fas fa-search me-1"></i> Cari Pelatihan
            </a>
        </div>

        <div id="historyContainer" class="d-flex flex-column gap-2">
            <!-- Rendered via JS -->
        </div>
    </div>
</div>

<!-- MODAL DETAIL TC & PELATIHAN -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header-custom text-center modal-header-sticky" id="modalHeaderContent">
                <button type="button" class="modal-close-btn shadow-sm" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 shadow-sm tc-icon-wrapper" style="width: 64px; height: 64px; background: white; color: var(--primary-color); font-size: 1.5rem;">
                    <i class="fas fa-building"></i>
                </div>
                <h3 class="fw-bold mb-1 tc-title-text" style="color: var(--text-main);" id="modalTcName">Nama Lembaga</h3>
                <p class="text-muted small mb-3 mx-auto tc-address-text" style="max-width: 500px;" id="modalTcAddress">
                    <i class="fas fa-map-marker-alt text-danger me-1"></i> Alamat Lengkap
                </p>
                <div class="d-flex flex-wrap justify-content-center align-items-center gap-2 tc-badges-wrapper">
                    <span class="badge rounded-pill px-3 py-2" style="background-color: rgba(16, 185, 129, 0.1); color: var(--secondary-color); border: 1px solid rgba(16,185,129,0.2);" id="modalTcScore">
                        <i class="fas fa-percentage me-1"></i> Skor: 0%
                    </span>
                    <button class="btn btn-sm rounded-pill fw-bold d-inline-flex align-items-center justify-content-center gap-2 px-3 py-1"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#scoreBreakdownCollapse"
                            aria-expanded="false"
                            aria-controls="scoreBreakdownCollapse"
                            id="btnToggleBreakdown"
                            style="border: 1px dashed var(--primary-color); background-color: rgba(79, 70, 229, 0.05); color: var(--primary-color); font-size: 0.75rem;">
                        <i class="fas fa-plus transition-icon"></i> <span>Lihat perhitungan skor</span>
                    </button>
                </div>
            </div>

            <div class="modal-body p-4 p-md-5" id="modalBodyScrollable" style="background-color: var(--bg-color);">
                <div id="modalAlertContainer" class="mb-4 d-none">
                    <div class="alert rounded-4 d-flex align-items-center gap-3 border-0 shadow-sm" id="modalAlertBox" role="alert">
                        <div id="modalAlertIcon" style="font-size: 1.5rem;"></div>
                        <div id="modalAlertText" class="fw-medium"></div>
                    </div>
                </div>

                @include('components.score-breakdown')

                <!-- Info Pelatihan Detail -->
                <div class="card border-0 shadow-sm rounded-4 mt-4" style="background-color: var(--surface-color);">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3" style="color: var(--text-main); border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                            <i class="fas fa-book-open text-primary me-2"></i> Detail Pendaftaran
                        </h6>
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <small class="text-muted d-block">Status Pendaftaran</small>
                                <div id="modalEnrollmentStatus" class="fw-bold mt-1"></div>
                            </div>
                            <div class="col-sm-6">
                                <small class="text-muted d-block">Tanggal Daftar</small>
                                <div id="modalEnrollmentDate" class="fw-bold mt-1"></div>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-1 mt-3" id="modalPelJudul" style="color: var(--primary-color);">Judul</h5>
                        <div class="text-muted small mb-3 d-flex flex-wrap gap-3" id="modalPelMeta">
                            <!-- Metas -->
                        </div>
                        <p class="text-muted small mb-0" id="modalPelDeskripsi">Deskripsi</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let rawRecommendations = [];
    const detailModalElement = document.getElementById('detailModal');
    let detailModal = null;

    document.addEventListener('DOMContentLoaded', async () => {
        if(detailModalElement) {
            detailModal = new bootstrap.Modal(detailModalElement);
        }

        const loading = document.getElementById('loadingState');
        const empty = document.getElementById('emptyState');
        const container = document.getElementById('historyContainer');

        try {
            // Load enrollments
            const data = await window.authFetch(window.apiBase + '/enrollments').then(window.parseApi);

            // In parallel, try to load recommendations for the score breakdown
            window.authFetch(window.apiBase + '/recommendations')
                .then(window.parseApi)
                .then(recs => {
                    if (recs) rawRecommendations = recs;
                })
                .catch(err => console.warn('Failed to load recommendations', err));

            loading.classList.add('d-none');

            if (!data || data.length === 0) {
                empty.classList.remove('d-none');
                return;
            }

            data.forEach(item => {
                const pel = item.pelatihan;
                const tc = item.training_center;
                if(!pel || !tc) return;

                // Set dynamic attributes based on status
                let badgeBg = '';
                let badgeText = '';
                let icon = '';
                let statusText = '';

                // Map from legacy or new DB status
                let dbStatus = item.status;
                if (dbStatus === 'terdaftar') dbStatus = 'pending';
                if (dbStatus === 'aktif' || dbStatus === 'selesai') dbStatus = 'approved';
                if (dbStatus === 'batal') dbStatus = 'rejected';

                if (dbStatus === 'approved') {
                    badgeBg = 'rgba(16, 185, 129, 0.1)';
                    badgeText = 'var(--secondary-color)';
                    icon = 'fa-check-circle';
                    statusText = 'Terdaftar';
                } else if (dbStatus === 'rejected') {
                    badgeBg = 'rgba(239, 68, 68, 0.1)';
                    badgeText = 'var(--danger-color)';
                    icon = 'fa-times-circle';
                    statusText = 'Ditolak';
                } else {
                    // Default / Pending
                    badgeBg = 'rgba(245, 158, 11, 0.1)';
                    badgeText = 'var(--warning-color)';
                    icon = 'fa-clock';
                    statusText = 'Menunggu Persetujuan';
                }

                // Format Date
                const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                const dateStr = new Date(item.tanggal_daftar).toLocaleDateString('id-ID', dateOptions);

                // Setup alert info for modal processing later
                item.mappedStatus = dbStatus;
                item.dateStr = dateStr;
                item.statusText = statusText;
                item.badgeBg = badgeBg;
                item.badgeText = badgeText;
                item.statusIcon = icon;

                // Encode data for modal
                const encodedData = encodeURIComponent(JSON.stringify(item));

                const card = `
                    <div class="history-card">
                        <div class="history-content">
                            <div class="history-header">
                                <div class="history-title-area">
                                    <div class="history-icon-wrapper">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div>
                                        <h5>${pel.judul}</h5>
                                    </div>
                                </div>
                                <div class="status-badge-modern" style="background-color: ${badgeBg}; color: ${badgeText}; border: 1px solid ${badgeText}33;">
                                    <i class="fas ${icon}"></i> ${statusText}
                                </div>
                            </div>
                            <div class="history-info-rows">
                                <div class="history-info-row tc-name">
                                    <i class="fas fa-building opacity-75"></i> ${tc.nama}
                                </div>
                                <div class="history-info-row date-row">
                                    <i class="far fa-calendar-alt"></i> ${dateStr}
                                </div>
                            </div>
                        </div>
                        <div class="history-action">
                            <button type="button" class="btn-detail" onclick="openDetail('${encodedData}')">
                                <i class="fas fa-eye"></i> Lihat Detail <i class="fas fa-arrow-right arrow-icon"></i>
                            </button>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', card);
            });

        } catch (error) {
            loading.classList.add('d-none');
            empty.classList.remove('d-none');
        }
    });

    window.openDetail = function(encodedData) {
        if (!detailModal) return;
        const item = JSON.parse(decodeURIComponent(encodedData));
        const pel = item.pelatihan;
        const tc = item.training_center;

        document.getElementById('modalTcName').textContent = tc.nama;
        document.getElementById('modalTcAddress').innerHTML = `<i class="fas fa-map-marker-alt text-danger me-1"></i> ${tc.alamat}`;

        document.getElementById('modalPelJudul').textContent = pel.judul;
        document.getElementById('modalPelDeskripsi').textContent = pel.deskripsi || '-';

        document.getElementById('modalEnrollmentStatus').innerHTML = `<span class="badge rounded-pill" style="background-color: ${item.badgeBg}; color: ${item.badgeText}; border: 1px solid ${item.badgeText}33;"><i class="fas ${item.statusIcon} me-1"></i> ${item.statusText}</span>`;
        document.getElementById('modalEnrollmentDate').textContent = item.dateStr;

        const methodStr = pel.method ? `<span><i class="fas fa-chalkboard-teacher me-1 opacity-75"></i>${pel.method}</span>` : '';
        const levelStr = pel.required_skill ? `<span><i class="fas fa-layer-group me-1 opacity-75"></i>${pel.required_skill}</span>` : '';
        const catStr = pel.interest_category ? `<span><i class="fas fa-tag me-1 opacity-75"></i>${pel.interest_category}</span>` : '';

        document.getElementById('modalPelMeta').innerHTML = `${catStr}${methodStr}${levelStr}`;

        // Handle Modal Alert
        const alertContainer = document.getElementById('modalAlertContainer');
        const alertBox = document.getElementById('modalAlertBox');
        const alertIcon = document.getElementById('modalAlertIcon');
        const alertText = document.getElementById('modalAlertText');

        alertContainer.classList.remove('d-none');
        alertBox.className = 'alert rounded-4 d-flex align-items-center gap-3 border-0 shadow-sm'; // Reset classes

        if (item.mappedStatus === 'pending') {
            alertBox.classList.add('alert-warning');
            alertIcon.innerHTML = '<i class="fas fa-exclamation-circle text-warning"></i>';
            alertText.innerHTML = 'Pendaftaran Anda sedang menunggu verifikasi admin.';
        } else if (item.mappedStatus === 'approved') {
            alertBox.classList.add('alert-success');
            alertIcon.innerHTML = '<i class="fas fa-check-circle text-success"></i>';
            alertText.innerHTML = 'Selamat! Pendaftaran Anda telah disetujui.';
        } else if (item.mappedStatus === 'rejected') {
            alertBox.classList.add('alert-danger');
            alertIcon.innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
            alertText.innerHTML = 'Maaf, pendaftaran Anda belum dapat disetujui.';
        } else {
            alertContainer.classList.add('d-none');
        }

        // Find Recommendation for Score Breakdown
        const recommendation = rawRecommendations.find(r => r.training_center && r.training_center.id === tc.id);

        if (recommendation && recommendation.score) {
            document.getElementById('modalTcScore').innerHTML = `<i class="fas fa-percentage me-1"></i> Kecocokan: ${recommendation.score}%`;
            document.getElementById('modalTcScore').classList.remove('d-none');
            document.getElementById('btnToggleBreakdown').classList.remove('d-none');

            setTimeout(() => {
                renderScoreBreakdown(recommendation.score_breakdown, recommendation.score);
            }, 50);
        } else {
            document.getElementById('modalTcScore').classList.add('d-none');
            document.getElementById('btnToggleBreakdown').classList.add('d-none');
            const breakdownContainer = document.querySelector('.score-breakdown-container');
            if(breakdownContainer) breakdownContainer.classList.add('d-none');
        }

        detailModal.show();
    }

    // Breakdown Logic
    document.addEventListener('DOMContentLoaded', () => {
        document.body.addEventListener('show.bs.collapse', function(event) {
            if (event.target.id === 'scoreBreakdownCollapse') {
                const btnToggle = document.getElementById('btnToggleBreakdown');
                if(btnToggle) btnToggle.innerHTML = '<i class="fas fa-minus transition-icon"></i> <span>Sembunyikan perhitungan</span>';
            }
        });

        document.body.addEventListener('hide.bs.collapse', function(event) {
            if (event.target.id === 'scoreBreakdownCollapse') {
                const btnToggle = document.getElementById('btnToggleBreakdown');
                if(btnToggle) btnToggle.innerHTML = '<i class="fas fa-plus transition-icon"></i> <span>Lihat perhitungan skor</span>';
            }
        });
    });

    function renderScoreBreakdown(scoreBreakdown, finalScore) {
        const container = document.getElementById('breakdownContent');
        const breakdownContainer = document.querySelector('.score-breakdown-container');
        if(!container || !breakdownContainer) return;

        let interpretation = '';
        if (finalScore >= 90) interpretation = 'Sangat sesuai dengan preferensi Anda';
        else if (finalScore >= 75) interpretation = 'Sesuai dengan sebagian besar preferensi Anda';
        else if (finalScore >= 60) interpretation = 'Cukup sesuai dengan preferensi Anda';
        else interpretation = 'Kurang sesuai dengan preferensi Anda';

        const totalScoreEl = document.getElementById('breakdownTotalScore');
        if(totalScoreEl) {
            totalScoreEl.innerHTML = `
                <div class="text-end">
                    <div>${finalScore}%</div>
                    <div class="text-muted fw-normal mt-1" style="font-size: 0.75rem;">${interpretation}</div>
                </div>
            `;
        }

        if (!scoreBreakdown) {
            breakdownContainer.classList.add('d-none');
            return;
        }

        breakdownContainer.classList.remove('d-none');
        container.innerHTML = '';

        const factors = [
            { key: 'interest', title: 'Bidang Pelatihan' },
            { key: 'skill', title: 'Tingkat Keahlian' },
            { key: 'method', title: 'Metode Pelatihan' },
            { key: 'popularity', title: 'Popularitas' },
            { key: 'distance', title: 'Jarak' }
        ];

        factors.forEach(f => {
            const data = scoreBreakdown[f.key];
            if (!data) return;

            let statusIcon = '';
            let statusClass = '';
            let statusText = '';
            let barColor = '';

            if (data.status === 'match') {
                statusIcon = '<i class="fas fa-check"></i>';
                statusClass = 'text-success';
                if(f.key === 'interest' || f.key === 'skill') statusText = 'Sangat sesuai';
                else if(f.key === 'method') statusText = 'Sesuai';
                else if(f.key === 'popularity') statusText = 'Sangat populer';
                else if(f.key === 'distance') statusText = 'Sangat dekat';
                else statusText = 'Sangat sesuai';
                barColor = 'bg-success';
            } else if (data.status === 'partial') {
                statusIcon = '<i class="fas fa-adjust"></i>';
                statusClass = 'text-warning';
                if(f.key === 'interest' || f.key === 'skill') statusText = 'Cukup sesuai';
                else if(f.key === 'method') statusText = 'Sebagian sesuai';
                else if(f.key === 'popularity') statusText = 'Cukup populer';
                else if(f.key === 'distance') statusText = 'Cukup dekat';
                else statusText = 'Kecocokan sebagian';
                barColor = 'bg-warning';
            } else {
                statusIcon = '<i class="far fa-circle"></i>';
                statusClass = 'text-muted';
                if(f.key === 'interest' || f.key === 'skill' || f.key === 'method') statusText = 'Tidak sesuai';
                else if(f.key === 'popularity') statusText = 'Popularitas rendah';
                else if(f.key === 'distance') statusText = 'Relatif jauh';
                else statusText = 'Tidak menambah skor';
                barColor = 'bg-secondary';
            }

            const percent = (data.score / data.max) * 100;

            const html = `
                <div class="breakdown-item">
                    <div class="d-flex justify-content-between align-items-end mb-1">
                        <div>
                            <div class="fw-bold text-dark small">${f.title}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">${data.label}</div>
                        </div>
                        <div class="fw-bold small" style="color: var(--text-main);">${data.score} / ${data.max}</div>
                    </div>
                    <div class="progress" style="height: 6px; background-color: var(--border-color);">
                        <div class="progress-bar ${barColor}" role="progressbar" style="width: ${percent}%" aria-valuenow="${data.score}" aria-valuemin="0" aria-valuemax="${data.max}"></div>
                    </div>
                    <div class="text-end mt-1 ${statusClass}" style="font-size: 0.75rem; font-weight: 600;">
                        ${statusIcon} ${statusText}
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        });
    }
</script>
@endpush