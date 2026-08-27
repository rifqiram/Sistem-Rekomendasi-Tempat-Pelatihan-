<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>{{ config('app.name', 'Sistem Rekomendasi Tempat Pelatihan') }}</title>
        @fonts
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@300;400;600;700&display=swap" rel="stylesheet">
        <style>
            :root { font-family: 'Plus Jakarta Sans', sans-serif; }
            h1, h2, h3, .hero-heading { font-family: 'Sora', sans-serif; }
            html { scroll-behavior: smooth; }
            .gradient-text {
                background: linear-gradient(135deg, #1e40af, #06b6d4);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            .card-hover {
                transition: transform 0.25s ease, box-shadow 0.25s ease;
            }
            .card-hover:hover {
                transform: translateY(-4px);
                box-shadow: 0 24px 48px rgba(15, 23, 42, 0.12);
            }
            .btn-primary-hover {
                transition: transform 0.2s ease, background-color 0.2s ease;
            }
            .btn-primary-hover:hover { transform: translateY(-2px); }
            .hero-bg {
                background: radial-gradient(ellipse 80% 50% at 50% -10%, rgba(37,99,235,0.12) 0%, transparent 70%);
            }
            .noise-bg::before {
                content: '';
                position: absolute;
                inset: 0;
                background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
                pointer-events: none;
                opacity: 0.4;
            }
            .nav-link {
                position: relative;
                transition: color 0.2s;
            }
            .nav-link::after {
                content: '';
                position: absolute;
                bottom: -2px; left: 0;
                width: 0; height: 2px;
                background: #2563eb;
                transition: width 0.2s ease;
                border-radius: 2px;
            }
            .nav-link:hover::after { width: 100%; }
            .nav-link:hover { color: #1e40af; }
            .step-connector {
                position: relative;
            }
            .step-connector:not(:last-child)::after {
                content: '';
                position: absolute;
                top: 32px;
                right: -12px;
                width: 24px;
                height: 2px;
                background: linear-gradient(to right, #dbeafe, #93c5fd);
            }
        </style>
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased">

        {{-- NAVBAR --}}
        <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-100 shadow-sm">
            <div class="max-w-7xl mx-auto px-6">
                <nav class="flex items-center justify-between h-16">
                    <a href="#hero" class="flex items-center gap-2 font-extrabold text-xl text-slate-900 tracking-tight">
                        <span class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white text-sm font-black">SR</span>
                        <span>Sistem Rekomendasi</span>
                    </a>
                    <div class="hidden md:flex items-center gap-7 text-sm font-medium text-slate-500">
                        <a href="#hero"     class="nav-link">Beranda</a>
                        <a href="#about"    class="nav-link">Tentang Kami</a>
                        <a href="#programs" class="nav-link">Pelatihan</a>
                        <a href="#mentors"  class="nav-link">Lembaga Mitra</a>
                        <a href="#footer"   class="nav-link">Kontak</a>
                        <a href="{{ url('/user/login') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200 transition btn-primary-hover">
                            Login
                        </a>
                        <a href="{{ url('/user/register') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-600 text-white font-semibold hover:bg-blue-700 transition btn-primary-hover">
                            Daftar
                        </a>
                    </div>
                </nav>
            </div>
        </header>

        <main class="flex flex-col gap-24">

            {{-- HERO --}}
            <section id="hero" class="hero-bg relative overflow-hidden">
                <div class="max-w-7xl mx-auto px-6 py-20 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div class="max-w-2xl">
                        <span class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 text-xs font-bold tracking-widest uppercase px-4 py-2 rounded-full mb-6 border border-blue-100">
                            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                            Mesin Rekomendasi Pintar
                        </span>
                        <h1 class="hero-heading text-4xl md:text-5xl xl:text-6xl font-bold leading-[1.05] tracking-tight text-slate-900 mb-6">
                            Rekomendasi Tempat Pelatihan Presisi &
                            <span class="gradient-text"> Terpersonalisasi</span>
                        </h1>
                        <p class="text-slate-500 text-lg leading-relaxed mb-8 max-w-xl">
                            Sistem cerdas berbasis <i>Rule-Based Scoring</i> untuk menemukan lembaga pelatihan yang paling sesuai dengan profil, minat, tingkat kemampuan, dan lokasi Anda.
                        </p>
                        <div class="flex flex-wrap gap-4">
                            <a href="{{ url('/user/register') }}"
                               class="btn-primary-hover inline-flex items-center gap-2 px-6 py-3 rounded-full bg-blue-600 text-white font-bold shadow-lg shadow-blue-200 hover:bg-blue-700">
                                Mulai Kuesioner
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </a>
                            <a href="#mentors"
                               class="btn-primary-hover inline-flex items-center gap-2 px-6 py-3 rounded-full bg-white text-slate-800 font-bold border border-slate-200 shadow hover:bg-slate-50">
                                Eksplorasi Lembaga
                            </a>
                        </div>
                    </div>

                    {{-- Hero Card --}}
                    <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-2xl shadow-slate-100 card-hover">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-2xl bg-blue-600 flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h2 class="text-xl font-bold text-slate-900">Kriteria Pencocokan</h2>
                        </div>
                        <ul class="space-y-5">
                            <li class="flex items-start gap-3">
                                <span class="mt-1 w-5 h-5 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </span>
                                <span class="text-slate-600 leading-relaxed">Pemetaan mendalam berdasarkan minat & skill Anda.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 w-5 h-5 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </span>
                                <span class="text-slate-600 leading-relaxed">Analisis radius lokasi untuk pelatihan terdekat.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 w-5 h-5 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </span>
                                <span class="text-slate-600 leading-relaxed">Perhitungan skor relevansi objektif secara real-time.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            {{-- TENTANG KAMI --}}
            <section id="about" class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-12">
                    <span class="inline-block text-xs font-bold tracking-widest text-blue-600 uppercase mb-3">Siapa Kami</span>
                    <h2 class="hero-heading text-3xl md:text-4xl font-bold text-slate-900 mb-4">Platform Agregator Pelatihan</h2>
                    <p class="text-slate-500 leading-relaxed max-w-2xl mx-auto text-base">
                        Kami menggunakan pemeringkatan <i>Rule-Based Scoring</i> untuk menganalisis data profil Anda dan merekomendasikan lembaga pelatihan yang memiliki indeks kecocokan tertinggi. Pendekatan analitik kami menjamin setiap rekomendasi relevan dan terukur.
                    </p>
                </div>
            </section>

            {{-- MENGAPA MEMILIH KAMI --}}
            <section class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-12">
                    <span class="inline-block text-xs font-bold tracking-widest text-blue-600 uppercase mb-3">Keunggulan</span>
                    <h2 class="hero-heading text-3xl md:text-4xl font-bold text-slate-900">Mengapa Sistem Kami Berbeda?</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @php
                        $features = [
                            ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'label' => 'Akurasi Tinggi', 'title' => 'Presisi Algoritma', 'desc' => 'Sistem menghitung skor kecocokan Anda dengan setiap lembaga pelatihan menggunakan metode Rule-Based Scoring.'],
                            ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Data Real-time', 'title' => 'Analisis Profil', 'desc' => 'Setiap input kuesioner Anda diolah secara otomatis untuk mempersempit pilihan yang paling tepat.'],
                            ['icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'label' => 'Geolokasi', 'title' => 'Filter Lokasi', 'desc' => 'Memberikan rekomendasi lembaga yang terdekat dari lokasi Anda untuk meminimalisir waktu dan biaya.'],
                            ['icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4', 'label' => 'Transparan', 'title' => 'Objektif', 'desc' => 'Pemeringkatan didasarkan sepenuhnya pada kriteria teknis yang Anda masukkan tanpa bias.'],
                            ['icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z', 'label' => 'Terpercaya', 'title' => 'Database Luas', 'desc' => 'Sistem kami terhubung dengan puluhan data lembaga pelatihan yang sudah terverifikasi dan bereputasi.'],
                            ['icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'label' => 'Dukungan', 'title' => 'Dukungan Pelanggan', 'desc' => 'Tim kami siap memandu jika Anda memerlukan bantuan dalam mengisi kuesioner profil Anda.']
                        ];
                    @endphp
                    @foreach($features as $f)
                    <div class="card-hover group bg-white rounded-3xl p-7 border border-slate-200 shadow-sm">
                        <div class="w-11 h-11 rounded-2xl bg-blue-50 group-hover:bg-blue-600 flex items-center justify-center mb-5 transition-colors duration-300">
                            <svg class="w-5 h-5 text-blue-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $f['icon'] }}"/>
                            </svg>
                        </div>
                        <span class="inline-block text-xs font-bold text-blue-600 tracking-widest uppercase mb-2">{{ $f['label'] }}</span>
                        <h3 class="font-bold text-slate-900 text-lg mb-2">{{ $f['title'] }}</h3>
                        <p class="text-slate-500 leading-relaxed text-sm">{{ $f['desc'] }}</p>
                    </div>
                    @endforeach
                </div>
            </section>

            {{-- PROGRAM PELATIHAN --}}
            <section id="programs" class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-12">
                    <span class="inline-block text-xs font-bold tracking-widest text-blue-600 uppercase mb-3">Kategori</span>
                    <h2 class="hero-heading text-3xl md:text-4xl font-bold text-slate-900 mb-4">Cakupan Minat Pelatihan</h2>
                    <p class="text-slate-500">Sistem kami memetakan lembaga berdasarkan minat dan lokasi terdekat dengan Anda, berikut kategori yang tersedia</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @php
                        $colors = [
                            'bg-red-50 text-red-600',
                            'bg-blue-50 text-blue-600',
                            'bg-emerald-50 text-emerald-600',
                            'bg-purple-50 text-purple-600',
                            'bg-orange-50 text-orange-600',
                            'bg-sky-50 text-sky-600',
                            'bg-amber-50 text-amber-600',
                            'bg-rose-50 text-rose-600'
                        ];
                    @endphp
                    @forelse($trainingCategories as $i => $cat)
                    <div class="card-hover relative bg-white rounded-3xl p-7 border border-slate-200 shadow-sm overflow-hidden flex flex-col items-start text-left">
                        <div class="absolute top-5 right-5 text-4xl font-black text-slate-100 select-none leading-none">{{ str_pad($i+1, 2, '0', STR_PAD_LEFT) }}</div>
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold mb-4 {{ $colors[$i % count($colors)] }}">
                            {{ $cat->count }} Program
                        </div>
                        <h3 class="font-bold text-slate-900 text-lg mb-2 leading-snug">{{ $cat->title }}</h3>
                        <p class="text-slate-500 text-sm leading-relaxed line-clamp-3">
                            Mencakup: {{ $cat->desc ?: 'Berbagai pelatihan terkait ' . strtolower($cat->title) }}
                        </p>
                    </div>
                    @empty
                    <div class="col-span-full text-center py-8 text-slate-500">
                        Kategori pelatihan belum tersedia.
                    </div>
                    @endforelse
                </div>
            </section>

            {{-- STATISTIK --}}
            <section class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-10">
                    <span class="inline-block text-xs font-bold tracking-widest text-blue-600 uppercase mb-3">Angka Bicara</span>
                    <h2 class="hero-heading text-3xl md:text-4xl font-bold text-slate-900">Database Sistem</h2>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="card-hover bg-white rounded-3xl p-7 text-center border border-slate-200 shadow-sm">
                        <strong class="block text-4xl font-extrabold text-blue-600 mb-2">{{ number_format($userCount ?? 0) }}+</strong>
                        <span class="text-slate-500 text-sm font-medium">User Terdaftar</span>
                    </div>
                    <div class="card-hover bg-white rounded-3xl p-7 text-center border border-slate-200 shadow-sm">
                        <strong class="block text-4xl font-extrabold text-emerald-600 mb-2">{{ number_format($centerCount ?? 0) }}+</strong>
                        <span class="text-slate-500 text-sm font-medium">Lembaga Tervalidasi</span>
                    </div>
                    <div class="card-hover bg-white rounded-3xl p-7 text-center border border-slate-200 shadow-sm">
                        <strong class="block text-4xl font-extrabold text-purple-600 mb-2">{{ $criteriaCount ?? 0 }}</strong>
                        <span class="text-slate-500 text-sm font-medium">Kriteria Penilaian</span>
                    </div>
                    <div class="card-hover bg-white rounded-3xl p-7 text-center border border-slate-200 shadow-sm">
                        <strong class="block text-4xl font-extrabold text-orange-600 mb-2">{{ number_format($recommendationCount ?? 0) }}+</strong>
                        <span class="text-slate-500 text-sm font-medium">Rekomendasi Dicetak</span>
                    </div>
                </div>
            </section>

            {{-- MENTOR --}}
            <section id="mentors" class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-12">
                    <span class="inline-block text-xs font-bold tracking-widest text-blue-600 uppercase mb-3">Mitra Kami</span>
                    <h2 class="hero-heading text-3xl md:text-4xl font-bold text-slate-900">Lembaga Pelatihan Tervalidasi</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @php
                        $avatarColors = ['bg-blue-100 text-blue-700', 'bg-purple-100 text-purple-700', 'bg-emerald-100 text-emerald-700', 'bg-orange-100 text-orange-700', 'bg-rose-100 text-rose-700'];
                    @endphp
                    @forelse($featuredCenters as $idx => $center)
                    <div class="card-hover flex items-start gap-4 bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-extrabold text-sm flex-shrink-0 {{ $avatarColors[$idx % count($avatarColors)] }}">
                            {{ strtoupper(substr($center->nama, 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 mb-1">{{ $center->nama }}</h3>
                            <p class="text-slate-500 text-sm leading-relaxed line-clamp-2" title="{{ $center->deskripsi }}">
                                {{ $center->deskripsi ?? 'Lembaga Pelatihan Profesional.' }}
                            </p>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full text-center py-8 text-slate-500">
                        Belum ada data lembaga pelatihan yang tersedia.
                    </div>
                    @endforelse
                </div>
            </section>

            {{-- CARA MENDAFTAR --}}
            <section class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-12">
                    <span class="inline-block text-xs font-bold tracking-widest text-blue-600 uppercase mb-3">Proses</span>
                    <h2 class="hero-heading text-3xl md:text-4xl font-bold text-slate-900">Cara Kerja Sistem Rekomendasi</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @php
                        $steps = [
                            ['num' => '01', 'title' => 'Buat Profil Akun', 'desc' => 'Daftar dan lengkapi data demografi dasar Anda sebagai peserta.'],
                            ['num' => '02', 'title' => 'Isi Kuesioner', 'desc' => 'Jawab pertanyaan sistem terkait minat, skill, dan preferensi lokasi.'],
                            ['num' => '03', 'title' => 'Algoritma Memproses', 'desc' => 'Sistem Rule-Based Scoring akan menghitung skor kecocokan tertinggi.'],
                            ['num' => '04', 'title' => 'Terima Rekomendasi', 'desc' => 'Dapatkan daftar tempat pelatihan yang paling relevan untuk Anda.'],
                        ];
                    @endphp
                    @foreach($steps as $step)
                    <div class="card-hover relative bg-white rounded-3xl p-7 border border-slate-200 shadow-sm text-center">
                        <div class="w-14 h-14 rounded-2xl bg-blue-600 text-white font-extrabold text-xl flex items-center justify-center mx-auto mb-5">
                            {{ $step['num'] }}
                        </div>
                        <h3 class="font-bold text-slate-900 text-base mb-2">{{ $step['title'] }}</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">{{ $step['desc'] }}</p>
                    </div>
                    @endforeach
                </div>
            </section>

            {{-- TESTIMONI --}}
            <section class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-12">
                    <span class="inline-block text-xs font-bold tracking-widest text-blue-600 uppercase mb-3">Testimonial</span>
                    <h2 class="hero-heading text-3xl md:text-4xl font-bold text-slate-900">Apa Kata Pengguna?</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @php
                        $testimonials = [
                            ['quote' => '"Sistem ini sangat akurat mencocokkan kriteria biaya dan lokasi yang saya miliki dengan tempat pelatihan terbaik."', 'name' => 'Rifqi Ramadhan'],
                            ['quote' => '"Kuesionernya mudah dipahami dan rekomendasi yang keluar benar-benar sesuai dengan minat belajar saya."', 'name' => 'Aulia Putri'],
                            ['quote' => '"Sangat menghemat waktu pencarian. Tidak perlu lagi membandingkan puluhan tempat pelatihan secara manual."', 'name' => 'Muhammad Rizki'],
                        ];
                    @endphp
                    @foreach($testimonials as $t)
                    <div class="card-hover bg-white rounded-3xl p-7 border border-slate-200 shadow-sm flex flex-col gap-5">
                        <div class="flex gap-1">
                            @for($i=0;$i<5;$i++)
                            <svg class="w-4 h-4 text-amber-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                        </div>
                        <p class="text-slate-600 leading-relaxed text-sm flex-1">{{ $t['quote'] }}</p>
                        <strong class="text-slate-900 text-sm font-bold">— {{ $t['name'] }}</strong>
                    </div>
                    @endforeach
                </div>
            </section>

            {{-- CTA BANNER --}}
            <section class="max-w-7xl mx-auto px-6">
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-blue-950 to-blue-700 text-white px-10 py-16 text-center">
                    <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 50%, #60a5fa 0%, transparent 50%), radial-gradient(circle at 80% 20%, #818cf8 0%, transparent 40%);"></div>
                    <div class="relative z-10">
                        <h2 class="hero-heading text-3xl md:text-4xl font-bold mb-4">Temukan Tempat Pelatihan Anda Hari Ini</h2>
                        <p class="text-blue-100 leading-relaxed max-w-2xl mx-auto mb-8">
                            Buat akun sekarang, lengkapi profil Anda, dan dapatkan rekomendasi dalam hitungan detik.
                        </p>
                        <div class="flex flex-wrap justify-center gap-4">
                            <a href="{{ url('/user/register') }}" class="btn-primary-hover inline-flex items-center gap-2 px-7 py-3.5 rounded-full bg-white text-slate-900 font-bold shadow-lg hover:bg-blue-50 transition">
                                Mulai Kuesioner
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

        </main>

        {{-- FOOTER --}}
        <footer id="footer" class="mt-24 border-t border-slate-100 bg-white">
            <div class="max-w-7xl mx-auto px-6 py-14">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-10">
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white text-sm font-black">SR</span>
                            <span class="font-extrabold text-lg text-slate-900">Sistem Rekomendasi Tempat Pelatihan</span>
                        </div>
                        <p class="text-slate-500 text-sm mb-1">Magetan, Jawa Timur</p>
                        <p class="text-slate-500 text-sm mb-1">Email: admin@sistemrekomendasi.test</p>
                        <p class="text-slate-500 text-sm">Telepon: 0812-xxxx-xxxx</p>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 mb-4">Menu</h3>
                        <div class="flex flex-wrap gap-x-6 gap-y-3 text-sm text-slate-500">
                            <a href="#hero" class="hover:text-blue-600 transition">Beranda</a>
                            <a href="#about" class="hover:text-blue-600 transition">Tentang Kami</a>
                            <a href="#programs" class="hover:text-blue-600 transition">Pelatihan</a>
                            <a href="#mentors" class="hover:text-blue-600 transition">Lembaga Mitra</a>
                            <a href="#footer" class="hover:text-blue-600 transition">Kontak</a>
                            <a href="{{ url('/user/login') }}" class="hover:text-blue-600 transition">Login</a>
                        </div>
                    </div>
                </div>
                <div class="pt-6 border-t border-slate-100 flex flex-wrap justify-between gap-4 text-sm text-slate-400">
                    <span>© 2026 Sistem Rekomendasi Tempat Pelatihan. All Rights Reserved.</span>
                </div>
            </div>
        </footer>
    </body>
</html>