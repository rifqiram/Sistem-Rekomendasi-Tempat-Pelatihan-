@extends('layouts.user')

@section('title', 'Kuesioner Preferensi')

@push('styles')
<style>
    /* Form & Input Reset (Consistent with Profile) */
    .form-control, .form-select {
        border-color: var(--border-color);
        padding: 0.75rem 1rem;
        border-radius: var(--radius-md);
        font-size: 0.95rem;
        color: var(--text-main);
        transition: border-color 0.2s, box-shadow 0.2s;
        background-color: var(--bg-color);
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        background-color: var(--surface-color);
    }

    .question-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .question-desc {
        color: var(--text-muted);
        font-size: 0.85rem;
        margin-bottom: 1.25rem;
        font-weight: 500;
    }

    /* Custom Radio Cards - Modernized */
    .radio-card-wrapper {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .radio-card-input {
        display: none;
    }

    .radio-card-label {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        padding: 1.25rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        background: var(--surface-color);
    }

    .radio-card-label:hover {
        border-color: rgba(79, 70, 229, 0.4);
        background-color: var(--bg-color);
        transform: translateY(-2px);
    }

    .radio-card-input:checked + .radio-card-label {
        border-color: var(--primary-color);
        background-color: rgba(79, 70, 229, 0.05);
        box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.1);
    }

    .radio-card-title {
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 0.25rem;
        font-size: 1rem;
        transition: color 0.2s;
    }

    .radio-card-input:checked + .radio-card-label .radio-card-title {
        color: var(--primary-color);
    }

    .radio-card-text {
        font-size: 0.8rem;
        color: var(--text-muted);
        line-height: 1.4;
    }

    .radio-icon {
        margin-bottom: 0.75rem;
        font-size: 1.5rem;
        color: var(--text-muted);
        transition: color 0.2s;
    }

    .radio-card-input:checked + .radio-card-label .radio-icon {
        color: var(--primary-color);
    }

    /* Modern Loading Overlay */
    #loadingOverlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        z-index: 9999;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .spinner-border-modern {
        width: 3.5rem; height: 3.5rem;
        border-width: 0.25em;
        color: var(--primary-color);
    }
</style>
@endpush

@section('content')
<div id="loadingOverlay" class="d-none">
    <div class="spinner-border spinner-border-modern mb-4" role="status"></div>
    <h4 class="fw-bold" style="color: var(--text-main);">Menganalisis Kecocokan...</h4>
    <p style="color: var(--text-muted);">Sistem Cerdas sedang memproses dan memetakan preferensi Anda.</p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        {{-- Header Page --}}
        <div class="d-flex align-items-center mb-4 gap-3">
            <div class="bg-emerald-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.25rem; background-color: rgba(16, 185, 129, 0.1); color: var(--secondary-color);">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0" style="color: var(--text-main);">Kuesioner Preferensi</h3>
                <p class="text-muted mb-0 small">Jawaban Anda menentukan hasil akurasi rekomendasi sistem.</p>
            </div>
        </div>

        <form id="kuesionerForm" novalidate>

            <!-- Pertanyaan 1: Bidang -->
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--surface-color);">
                <div class="card-body p-4 p-md-5">
                    <div class="question-title">
                        <span class="badge rounded-pill me-1" style="background-color: var(--primary-color);">1</span>
                        Bidang pelatihan apa yang paling Anda minati? <span class="text-danger ms-1">*</span>
                    </div>
                    <div class="question-desc">Sistem akan memprioritaskan hasil untuk bidang keahlian spesifik ini (Bobot Algoritma: 35%).</div>

                    <select class="form-select" id="q_bidang" required>
                        <option value="" disabled selected>-- Pilih Kategori Bidang --</option>
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
            </div>

            <!-- Pertanyaan 2: Skill Level -->
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--surface-color);">
                <div class="card-body p-4 p-md-5">
                    <div class="question-title">
                        <span class="badge rounded-pill me-1" style="background-color: var(--primary-color);">2</span>
                        Bagaimana tingkat keahlian dasar Anda saat ini? <span class="text-danger ms-1">*</span>
                    </div>
                    <div class="question-desc">Digunakan untuk menyeleksi kurikulum dan tingkat kesulitan agar sesuai dengan Anda (Bobot Algoritma: 20%).</div>

                    <div class="radio-card-wrapper">
                        <div>
                            <input type="radio" name="q_skill" id="skill_beginner" value="Beginner" class="radio-card-input" required>
                            <label for="skill_beginner" class="radio-card-label">
                                <span class="radio-card-title">Beginner</span>
                                <span class="radio-card-text">Pemula, mempelajari dari nol.</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" name="q_skill" id="skill_intermediate" value="Intermediate" class="radio-card-input">
                            <label for="skill_intermediate" class="radio-card-label">
                                <span class="radio-card-title">Intermediate</span>
                                <span class="radio-card-text">Sudah paham dasar, butuh level menengah.</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" name="q_skill" id="skill_advanced" value="Advanced" class="radio-card-input">
                            <label for="skill_advanced" class="radio-card-label">
                                <span class="radio-card-title">Advanced</span>
                                <span class="radio-card-text">Tingkat mahir, studi kasus kompleks.</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pertanyaan 3: Metode -->
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--surface-color);">
                <div class="card-body p-4 p-md-5">
                    <div class="question-title">
                        <span class="badge rounded-pill me-1" style="background-color: var(--primary-color);">3</span>
                        Metode pelatihan seperti apa yang Anda inginkan? <span class="text-danger ms-1">*</span>
                    </div>
                    <div class="question-desc">Mencocokkan fasilitas dan kehadiran (Bobot Algoritma: 15%).</div>

                    <div class="radio-card-wrapper">
                        <div>
                            <input type="radio" name="q_metode" id="metode_online" value="Online" class="radio-card-input" required>
                            <label for="metode_online" class="radio-card-label">
                                <i class="fas fa-laptop radio-icon"></i>
                                <span class="radio-card-title">Online</span>
                                <span class="radio-card-text">Daring penuh dari rumah (Zoom/Meet).</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" name="q_metode" id="metode_offline" value="Offline" class="radio-card-input">
                            <label for="metode_offline" class="radio-card-label">
                                <i class="fas fa-building radio-icon"></i>
                                <span class="radio-card-title">Offline (Luring)</span>
                                <span class="radio-card-text">Hadir secara fisik di lokasi pelatihan.</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" name="q_metode" id="metode_hybrid" value="Hybrid" class="radio-card-input">
                            <label for="metode_hybrid" class="radio-card-label">
                                <i class="fas fa-sync-alt radio-icon"></i>
                                <span class="radio-card-title">Hybrid</span>
                                <span class="radio-card-text">Kombinasi antara kelas tatap muka & daring.</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pertanyaan 4: Jarak -->
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--surface-color);">
                <div class="card-body p-4 p-md-5">
                    <div class="question-title">
                        <span class="badge rounded-pill me-1" style="background-color: var(--primary-color);">4</span>
                        Jarak maksimal radius lokasi pelatihan yang dapat Anda tempuh? <span class="text-danger ms-1">*</span>
                    </div>
                    <div class="question-desc">Kalkulasi menggunakan <i>Haversine Formula</i> berdasarkan titik lokasi profil Anda (Bobot Algoritma: 20%).</div>

                    <div class="input-group">
                        <input type="number" class="form-control form-control-lg" id="q_jarak" min="1" max="1000" value="50" required style="max-width: 150px; background-color: var(--surface-color);">
                        <span class="input-group-text border-start-0 text-muted" style="background-color: var(--surface-color);">Kilometer (KM)</span>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-5">
                <button type="submit" class="btn fw-bold px-5 py-3 rounded-pill shadow-sm" id="btnSubmit" style="background-color: var(--primary-color); color: white; transition: transform 0.2s;">
                    <i class="fas fa-magic me-2"></i> Temukan Rekomendasi Saya
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        await loadKuesioner();
    });

    async function loadKuesioner() {
        try {
            const data = await window.authFetch(window.apiBase + '/questionnaire').then(window.parseApi);
            if (data) {
                if(data.bidang_diminati) document.getElementById('q_bidang').value = data.bidang_diminati;

                if(data.tingkat_keahlian) {
                    const el = document.querySelector(`input[name="q_skill"][value="${data.tingkat_keahlian}"]`);
                    if(el) el.checked = true;
                }

                if(data.metode_pelatihan) {
                    const el = document.querySelector(`input[name="q_metode"][value="${data.metode_pelatihan}"]`);
                    if(el) el.checked = true;
                }

                if(data.jarak_maksimal) {
                    document.getElementById('q_jarak').value = data.jarak_maksimal;
                }
            }
        } catch (e) {
            // No questionnaire data yet
        }
    }

    document.getElementById('kuesionerForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        // Ambil value dari form
        const bidang = document.getElementById('q_bidang').value;
        const skillEl = document.querySelector('input[name="q_skill"]:checked');
        const metodeEl = document.querySelector('input[name="q_metode"]:checked');
        const jarak = document.getElementById('q_jarak').value;

        let missingFields = [];
        if (!bidang) missingFields.push("Bidang Pelatihan");
        if (!skillEl) missingFields.push("Tingkat Keahlian");
        if (!metodeEl) missingFields.push("Metode Pelatihan");
        if (!jarak) missingFields.push("Jarak Maksimal");

        if (jarak && (parseInt(jarak) <= 0 || parseInt(jarak) > 100)) {
            Swal.fire({
                icon: 'warning',
                title: 'Jarak Tidak Valid',
                text: 'Maksimal jarak yang dapat Anda masukkan adalah 100 KM.',
                confirmButtonText: 'Perbaiki',
                customClass: {
                    confirmButton: 'btn btn-primary rounded-pill px-4',
                    popup: 'rounded-4'
                },
                buttonsStyling: false
            });
            return;
        }

        if (missingFields.length > 0) {
            const missingText = missingFields.join(', ');
            
            // SweetAlert custom render html content instead of plaintext 'text'
            Swal.fire({
                icon: 'error',
                title: 'Data Belum Lengkap',
                html: `Mohon lengkapi isian yang masih kosong: <b>${missingText}</b> sebelum melanjutkan.`,
                confirmButtonText: 'Periksa Kembali',
                customClass: {
                    confirmButton: 'btn btn-primary rounded-pill px-4',
                    popup: 'rounded-4'
                },
                buttonsStyling: false
            });
            return;
        }

        const payload = {
            answers: {
                bidang_diminati: bidang,
                tingkat_keahlian: skillEl.value,
                metode_pelatihan: metodeEl.value,
                jarak_maksimal: jarak
            }
        };

        const overlay = document.getElementById('loadingOverlay');
        overlay.classList.remove('d-none'); // Tampilkan UI Loading

        try {
            const response = await window.authFetch(window.apiBase + '/questionnaire', {
                method: 'POST',
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                const resData = await response.json();
                throw new Error(resData.message || 'Gagal memproses rekomendasi.');
            }

            // Engine selesai
            window.location.href = '/user/recommendations';

        } catch (error) {
            overlay.classList.add('d-none');
            window.showError('Gagal Memproses', error.message);
        }
    });
</script>
@endpush