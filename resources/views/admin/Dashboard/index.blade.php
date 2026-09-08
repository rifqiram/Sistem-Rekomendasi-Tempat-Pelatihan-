@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page_title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@push('styles')
<style>
    /* Modern Metric Cards for Admin Dashboard */
    .metric-card {
        background-color: #fff;
        border-radius: 0.75rem;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        position: relative;
        overflow: hidden;
        height: 100%;
        text-decoration: none !important;
    }

    .metric-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border-color: rgba(0,0,0,0.1);
    }

    .metric-icon-wrapper {
        width: 60px;
        height: 60px;
        border-radius: 50rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        flex-shrink: 0;
    }

    .metric-info {
        display: flex;
        flex-direction: column;
    }

    .metric-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .metric-label {
        font-size: 0.875rem;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .metric-link-indicator {
        position: absolute;
        top: 1rem;
        right: 1rem;
        color: #cbd5e1;
        font-size: 1rem;
        transition: color 0.3s, transform 0.3s;
    }

    .metric-card:hover .metric-link-indicator {
        color: var(--bs-primary);
        transform: translateX(3px) scale(1.1);
    }

    /* Table Enhancements */
    .table-modern thead th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0;
        padding-top: 1rem;
        padding-bottom: 1rem;
    }
    .table-modern td {
        vertical-align: middle;
        padding-top: 1rem;
        padding-bottom: 1rem;
        color: #334155;
    }
</style>
@endpush

@section('content')
<!-- Metric Cards Row -->
<div class="row g-3 mb-4">
    <!-- Training Center -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.training-centers.index') }}" class="metric-card">
            <div class="metric-icon-wrapper" style="background-color: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                <i class="fas fa-building"></i>
            </div>
            <div class="metric-info">
                <span class="metric-value" id="stat-tc">0</span>
                <span class="metric-label">Training Center</span>
            </div>
            <i class="fas fa-chevron-right metric-link-indicator"></i>
        </a>
    </div>

    <!-- Pelatihan Aktif -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.pelatihan.index') }}" class="metric-card">
            <div class="metric-icon-wrapper" style="background-color: rgba(16, 185, 129, 0.1); color: #10b981;">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="metric-info">
                <span class="metric-value" id="stat-pelatihan">0</span>
                <span class="metric-label">Pelatihan Aktif</span>
            </div>
            <i class="fas fa-chevron-right metric-link-indicator"></i>
        </a>
    </div>

    <!-- Users -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.users.index') }}" class="metric-card">
            <div class="metric-icon-wrapper" style="background-color: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                <i class="fas fa-users"></i>
            </div>
            <div class="metric-info">
                <span class="metric-value" id="stat-users">0</span>
                <span class="metric-label">Pengguna</span>
            </div>
            <i class="fas fa-chevron-right metric-link-indicator"></i>
        </a>
    </div>

    <!-- Enrollments -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.enrollments.index') }}" class="metric-card">
            <div class="metric-icon-wrapper" style="background-color: rgba(79, 70, 229, 0.1); color: #4f46e5;">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div class="metric-info">
                <span class="metric-value" id="stat-enrollments">0</span>
                <span class="metric-label">Pendaftaran</span>
            </div>
            <i class="fas fa-chevron-right metric-link-indicator"></i>
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius: 0.75rem;">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center" style="border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem;">
                <h3 class="card-title fw-bold mb-0 text-dark"><i class="fas fa-chart-bar text-primary me-2"></i> Grafik Popularitas Pelatihan (Top 5)</h3>
            </div>
            <div class="card-body">
                <canvas id="popularityChart" style="min-height: 300px; max-height: 350px; width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Pendaftar Terbaru -->
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius: 0.75rem;">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center" style="border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem;">
                <h3 class="card-title fw-bold mb-0 text-dark me-auto"><i class="fas fa-history text-muted me-2"></i> Pendaftar Terbaru</h3>
                <a href="{{ route('admin.enrollments.index') }}" class="btn btn-sm btn-light border fw-semibold text-primary">
                    <i class="fas fa-external-link-alt me-1"></i> Lihat Semua
                </a>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-modern mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Peserta (User)</th>
                            <th>Modul Pelatihan</th>
                            <th>Lembaga (TC)</th>
                            <th>Tgl Pendaftaran</th>
                            <th class="pe-4">Status</th>
                        </tr>
                    </thead>
                    <tbody id="recent-enrollments">
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                                <div class="text-muted small">Memuat data pendaftar...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        try {
            const res = await window.authFetch(window.apiBase + '/admin/stats');
            const data = await res.json();
            const stats = data.data;

            // Metrics
            document.getElementById('stat-tc').textContent = stats.metrics.training_centers;
            document.getElementById('stat-pelatihan').textContent = stats.metrics.pelatihan;
            document.getElementById('stat-users').textContent = stats.metrics.users;
            document.getElementById('stat-enrollments').textContent = stats.metrics.enrollments;

            // Chart Popularitas
            const popularTrainings = stats.popular_trainings || [];
            
            // Check if there is at least one approved enrollment among the top 5
            const hasData = popularTrainings.some(t => t.approved_enrollments_count > 0);
            
            if (hasData) {
                const labels = popularTrainings.filter(t => t.approved_enrollments_count > 0).map(t => t.judul.length > 25 ? t.judul.substring(0, 25) + '...' : t.judul);
                const counts = popularTrainings.filter(t => t.approved_enrollments_count > 0).map(t => t.approved_enrollments_count);
                
                const ctx = document.getElementById('popularityChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Pendaftar Disetujui',
                            data: counts,
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1, precision: 0 }
                            }
                        },
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            } else {
                const ctxEl = document.getElementById('popularityChart');
                ctxEl.parentNode.innerHTML = '<div class="text-center text-muted py-5"><i class="fas fa-chart-bar fs-1 d-block mb-3 opacity-25"></i>Belum ada data popularitas (Belum ada pendaftar yang disetujui)</div>';
            }

            // Recent
            const tbody = document.getElementById('recent-enrollments');
            tbody.innerHTML = '';

            if(stats.recent_enrollments.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fs-2 mb-3 opacity-25"></i>
                            <div class="small fw-medium">Belum ada pendaftar terbaru</div>
                        </td>
                    </tr>`;
                return;
            }

            stats.recent_enrollments.forEach(item => {
                const user = item.user ? item.user.name : '-';
                const pel = item.pelatihan ? item.pelatihan.judul : '-';
                const tc = item.training_center ? item.training_center.nama : '-';

                // Format Date nicely
                const dateOptions = { day: 'numeric', month: 'short', year: 'numeric' };
                const date = new Date(item.created_at).toLocaleDateString('id-ID', dateOptions);

                // Map old legacy DB status to new workflow status
                let dbStatus = item.status;
                if (dbStatus === 'terdaftar') dbStatus = 'pending';
                if (dbStatus === 'aktif' || dbStatus === 'selesai') dbStatus = 'approved';
                if (dbStatus === 'batal') dbStatus = 'rejected';

                // Modern Status Badges mapping
                let statusBadge = '';
                if (dbStatus === 'pending') {
                    statusBadge = '<span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-semibold"><i class="fas fa-clock me-1"></i> Pending</span>';
                } else if (dbStatus === 'approved') {
                    statusBadge = '<span class="badge bg-success rounded-pill px-3 py-1 fw-semibold"><i class="fas fa-check-circle me-1"></i> Disetujui</span>';
                } else if (dbStatus === 'rejected') {
                    statusBadge = '<span class="badge bg-danger rounded-pill px-3 py-1 fw-semibold"><i class="fas fa-times-circle me-1"></i> Ditolak</span>';
                } else {
                    statusBadge = `<span class="badge bg-secondary rounded-pill px-3 py-1">${item.status}</span>`;
                }

                const tr = `
                    <tr>
                        <td class="ps-4 fw-bold text-dark">${user}</td>
                        <td class="fw-medium">${pel}</td>
                        <td class="text-muted"><i class="fas fa-building small me-1 opacity-50"></i> ${tc}</td>
                        <td class="text-muted small">${date}</td>
                        <td class="pe-4">${statusBadge}</td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', tr);
            });

        } catch (error) {
            console.error(error);
            const tbody = document.getElementById('recent-enrollments');
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-danger small"><i class="fas fa-exclamation-circle me-1"></i> Gagal memuat data</td></tr>`;
            window.showToast('Gagal memuat statistik', 'error');
        }
    });
</script>
@endpush