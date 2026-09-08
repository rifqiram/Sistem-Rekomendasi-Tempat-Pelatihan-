@extends('layouts.user')

@section('title', 'Dashboard')

@push('styles')
<style>
    /* Welcome Card Modernization */
    .welcome-card {
        background: linear-gradient(135deg, var(--primary-color), var(--info-color));
        border-radius: var(--radius-lg);
        padding: 2rem;
        color: #fff;
        box-shadow: var(--shadow-md);
        position: relative;
        overflow: hidden;
    }

    .welcome-card::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
    }

    .welcome-card h2 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
    }

    .welcome-card p {
        font-size: 0.95rem;
        opacity: 0.9;
        margin: 0;
        position: relative;
        z-index: 1;
    }

    /* Modern Quick Actions */
    .action-card {
        background-color: var(--surface-color);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        text-decoration: none;
        color: var(--text-main);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
        box-shadow: var(--shadow-sm);
    }

    .action-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
        border-color: var(--primary-color);
    }

    .action-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .action-card:hover .action-icon-wrapper {
        transform: scale(1.05);
    }

    /* Stats Card Modernization */
    .stat-card-modern {
        background-color: var(--surface-color);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: var(--shadow-sm);
        transition: box-shadow 0.3s ease;
    }

    .stat-card-modern:hover {
        box-shadow: var(--shadow-md);
    }

    .stat-icon-wrapper {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    /* Colors for Icons */
    .bg-indigo-subtle { background-color: rgba(79, 70, 229, 0.1); color: var(--primary-color); }
    .bg-emerald-subtle { background-color: rgba(16, 185, 129, 0.1); color: var(--secondary-color); }
    .bg-amber-subtle { background-color: rgba(245, 158, 11, 0.1); color: var(--warning-color); }
    .bg-blue-subtle { background-color: rgba(59, 130, 246, 0.1); color: var(--info-color); }

    /* Custom Progress Bar */
    .progress-custom {
        height: 10px;
        background-color: var(--border-color);
        border-radius: 50rem;
        overflow: hidden;
    }

    .progress-bar-custom {
        height: 100%;
        background: linear-gradient(90deg, var(--secondary-color), #34d399);
        border-radius: 50rem;
        transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }
    /* Trending List Custom */
    .trending-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .trending-item {
        background: var(--surface-color);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.2s ease;
    }
    
    .trending-item:hover {
        border-color: var(--primary-color);
        box-shadow: var(--shadow-sm);
        transform: translateX(4px);
    }
    
    .trending-rank {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        flex-shrink: 0;
        color: var(--text-muted);
        background: rgba(0,0,0,0.05);
    }
    
    .trending-rank.rank-1 { background: rgba(245, 158, 11, 0.15); color: #d97706; }
    .trending-rank.rank-2 { background: rgba(148, 163, 184, 0.2); color: #475569; }
    .trending-rank.rank-3 { background: rgba(180, 83, 9, 0.15); color: #92400e; }
    
    .trending-body {
        flex-grow: 1;
        min-width: 0;
    }
    
    .trending-title {
        font-weight: 700;
        color: var(--text-main);
        font-size: 0.95rem;
        margin-bottom: 0.15rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .trending-tc {
        font-size: 0.8rem;
        color: var(--text-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .trending-stats {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.5rem;
        white-space: nowrap;
    }
</style>
@endpush

@section('content')
    {{-- Welcome Card --}}
    <div class="welcome-card mb-4">
        <h2>Selamat datang, <span id="welcomeName">Pengguna</span>!</h2>
        <p>Mari tingkatkan karir Anda dengan menemukan program pelatihan terbaik yang sesuai dengan minat dan kualifikasi Anda.</p>
    </div>

    <div class="row g-4 mb-5">
        {{-- Left Column: Profile Status & Stats --}}
        <div class="col-lg-8">
            {{-- Profile Completeness Card --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--surface-color);">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="fw-bold mb-1" style="color: var(--text-main);">Kelengkapan Profil</h5>
                            <p class="text-muted mb-0 small" id="profileStatus">Menganalisis kelengkapan data diri...</p>
                        </div>
                        <h3 class="fw-bold mb-0" style="color: var(--secondary-color);" id="profilePercent">0%</h3>
                    </div>
                    <div class="progress-custom">
                        <div class="progress-bar-custom" id="profileBar" style="width:0%"></div>
                    </div>
                </div>
            </div>

            {{-- Stats Grid Native Bootstrap --}}
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="stat-card-modern">
                        <div class="stat-icon-wrapper bg-indigo-subtle">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold lh-1 mb-1" style="color: var(--text-main);" id="countKeahlian">0</div>
                            <div class="text-muted small fw-medium">Kuesioner Diisi</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card-modern">
                        <div class="stat-icon-wrapper bg-blue-subtle">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold lh-1 mb-1" style="color: var(--text-main);" id="countRekomendasi">0</div>
                            <div class="text-muted small fw-medium">Rekomendasi</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card-modern">
                        <div class="stat-icon-wrapper bg-amber-subtle">
                            <i class="fas fa-history"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold lh-1 mb-1" style="color: var(--text-main);" id="countEnrollment">0</div>
                            <div class="text-muted small fw-medium">Terdaftar</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Trending Trainings Card (Moved to Left Column) --}}
            <div class="card border-0 shadow-sm rounded-4 mt-4" style="background: var(--surface-color);">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <i class="fas fa-chart-line text-primary fs-4 me-2"></i>
                        <h5 class="fw-bold mb-0" style="color: var(--text-main);">Tempat Pelatihan Banyak Diminati</h5>
                    </div>
                    
                    <div id="trendingContainer" class="trending-list">
                        <div class="text-center py-4 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                            <div class="small">Memuat daftar pelatihan...</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right Column: Account Info --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: var(--surface-color);">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0" style="color: var(--text-main);">Informasi Akun</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex flex-column gap-3">
                        <div class="p-3 rounded-3" style="background-color: var(--bg-color);">
                            <span class="d-block text-muted small fw-semibold mb-1">NAMA LENGKAP</span>
                            <span class="fw-bold text-dark" id="infoName">-</span>
                        </div>
                        <div class="p-3 rounded-3" style="background-color: var(--bg-color);">
                            <span class="d-block text-muted small fw-semibold mb-1">ALAMAT EMAIL</span>
                            <span class="fw-bold text-dark text-break" id="infoEmail">-</span>
                        </div>
                        <div class="p-3 rounded-3" style="background-color: var(--bg-color);">
                            <span class="d-block text-muted small fw-semibold mb-1">HAK AKSES</span>
                            <span class="badge" style="background-color: var(--primary-color);" id="infoRole">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="mb-3">
        <h5 class="fw-bold mb-1" style="color: var(--text-main);">Aksi Cepat</h5>
        <p class="text-muted small">Navigasi pintas ke fitur utama platform</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-3">
            <a href="{{ route('user.profile') }}" class="action-card text-decoration-none">
                <div class="action-icon-wrapper bg-indigo-subtle">
                    <i class="fas fa-user-edit"></i>
                </div>
                <h6 class="fw-bold mb-1" style="color: var(--text-main);">Lengkapi Profil</h6>
                <p class="text-muted small mb-0 lh-sm">Perbarui data diri & lokasi</p>
            </a>
        </div>
        <div class="col-sm-6 col-md-3">
            <a href="{{ route('user.questionnaire') }}" class="action-card text-decoration-none">
                <div class="action-icon-wrapper bg-emerald-subtle">
                    <i class="fas fa-tasks"></i>
                </div>
                <h6 class="fw-bold mb-1" style="color: var(--text-main);">Isi Kuesioner</h6>
                <p class="text-muted small mb-0 lh-sm">Tentukan kriteria pelatihan</p>
            </a>
        </div>
        <div class="col-sm-6 col-md-3">
            <a href="{{ route('user.recommendations') }}" class="action-card text-decoration-none">
                <div class="action-icon-wrapper bg-blue-subtle">
                    <i class="fas fa-star"></i>
                </div>
                <h6 class="fw-bold mb-1" style="color: var(--text-main);">Rekomendasi</h6>
                <p class="text-muted small mb-0 lh-sm">Lihat pelatihan terbaik</p>
            </a>
        </div>
        <div class="col-sm-6 col-md-3">
            <a href="{{ route('user.enrollments') }}" class="action-card text-decoration-none">
                <div class="action-icon-wrapper bg-amber-subtle">
                    <i class="fas fa-history"></i>
                </div>
                <h6 class="fw-bold mb-1" style="color: var(--text-main);">Riwayat Pendaftaran</h6>
                <p class="text-muted small mb-0 lh-sm">Cek status pengajuan Anda</p>
            </a>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const user = window.getApiUser();
        const token = window.getApiToken();

        if (!token || !user || user.role !== 'user') {
            return; // Handled by layout
        }

        // Populate user info
        const displayName = user.name || user.email || 'Pengguna';
        document.getElementById('welcomeName').textContent = displayName.split(' ')[0];
        document.getElementById('infoName').textContent = user.name || '-';
        document.getElementById('infoEmail').textContent = user.email || '-';
        document.getElementById('infoRole').textContent = user.role ? user.role.toUpperCase() : '-';

        // Load dashboard stats
        async function loadDashboardData() {
            try {
                const profile = await window.authFetch(window.apiBase + '/profile').then(window.parseApi).catch(() => null);

                // GATEKEEPER LOGIC
                if (!profile || !profile.age || !profile.latitude) {
                    await window.showError('Profil Belum Lengkap', 'Anda harus melengkapi biodata dan alamat lokasi terlebih dahulu sebelum mengakses Dashboard.');
                    window.location.href = '/user/profile';
                    return;
                }

                const kuesioner = await window.authFetch(window.apiBase + '/questionnaire').then(window.parseApi).catch(() => null);

                if (!kuesioner || !kuesioner.bidang_diminati) {
                    await window.showError('Kuesioner Belum Diisi', 'Silakan isi preferensi kuesioner Anda agar sistem dapat merekomendasikan pelatihan yang tepat.');
                    window.location.href = '/user/questionnaire';
                    return;
                }

                // Update UI based on completeness
                const profilePercentEl = document.getElementById('profilePercent');
                const profileBarEl = document.getElementById('profileBar');
                const profileStatusEl = document.getElementById('profileStatus');

                profilePercentEl.textContent = '100%';
                profileBarEl.style.width = '100%';
                profileStatusEl.textContent = 'Profil & Kuesioner Anda sudah lengkap. Siap mencari pelatihan!';

                // Fetch Stats
                let rekomendasiCount = 0;
                try {
                    const rekom = await window.authFetch(window.apiBase + '/recommendations').then(window.parseApi);
                    rekomendasiCount = Array.isArray(rekom) ? rekom.length : 0;
                } catch (e) {}

                let enrollmentCount = 0;
                try {
                    const enrolls = await window.authFetch(window.apiBase + '/enrollments').then(window.parseApi);
                    enrollmentCount = Array.isArray(enrolls) ? enrolls.length : 0;
                } catch (e) {}

                document.getElementById('countKeahlian').textContent = kuesioner ? 1 : 0;
                document.getElementById('countRekomendasi').textContent = rekomendasiCount;
                document.getElementById('countEnrollment').textContent = enrollmentCount;

            } catch (e) {
                console.error('Dashboard load error:', e);
            }
        }

        // Load Trending Trainings
        async function loadTrendingTrainings() {
            try {
                const data = await window.authFetch(window.apiBase + '/trending-trainings').then(window.parseApi);
                const container = document.getElementById('trendingContainer');
                container.innerHTML = '';
                
                const items = data.data || data;
                
                if (!items || items.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-4 text-muted border rounded-3 bg-light">
                            <i class="fas fa-info-circle fs-3 mb-2 opacity-50"></i>
                            <div class="small fw-medium">Belum ada data popularitas</div>
                        </div>
                    `;
                    return;
                }
                
                items.forEach((item, index) => {
                    const rank = index + 1;
                    let rankClass = '';
                    if (rank === 1) rankClass = 'rank-1';
                    else if (rank === 2) rankClass = 'rank-2';
                    else if (rank === 3) rankClass = 'rank-3';
                    
                    const tcName = item.training_center ? item.training_center.nama : 'Lembaga tidak diketahui';
                    
                    container.insertAdjacentHTML('beforeend', `
                        <div class="trending-item">
                            <div class="trending-rank ${rankClass}">${rank}</div>
                            <div class="trending-body">
                                <div class="trending-title" title="${item.judul}">${item.judul}</div>
                                <div class="trending-tc" title="${tcName}"><i class="fas fa-building opacity-50 me-1"></i> ${tcName}</div>
                            </div>
                            <div class="trending-stats">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2 fw-semibold">
                                    <i class="fas fa-users me-1"></i> ${item.approved_count}
                                </span>
                            </div>
                        </div>
                    `);
                });
            } catch (err) {
                console.error(err);
                document.getElementById('trendingContainer').innerHTML = '<div class="text-danger small p-3 border rounded text-center">Gagal memuat data trending.</div>';
            }
        }

        loadDashboardData();
        loadTrendingTrainings();
    });
</script>
@endpush