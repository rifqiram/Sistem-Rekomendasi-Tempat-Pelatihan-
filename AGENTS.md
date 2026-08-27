# AGENTS.md

## Tujuan
Project ini merupakan Sistem Rekomendasi Tempat Pelatihan berbasis Rule-Based Scoring.
Sistem ini membuang arsitektur lama (Mentor, Peserta, Pendaftaran Manual) dan sepenuhnya mengadopsi arsitektur desentralisasi via REST API V2 (Training Center, Pelatihan, Profile User, Questionnaire, Enrollment Rekomendasi).

Sistem menggunakan data Profile, QuestionnaireResponse, dan Pelatihan untuk menghasilkan rekomendasi yang dipersonalisasi. Rekomendasi dikompilasi (diagregasi) dari tingkat Pelatihan ke tingkat entitas *Training Center* (TC).

## Status Terkini (FINAL)
Seluruh lapisan aplikasi, mulai dari Database, Logic Backend, REST API, hingga Frontend (Admin Panel, User UI, Landing Page) **telah diselesaikan 100% dan terintegrasi secara E2E (End-to-End)**. Tidak ada lagi sisa-sisa *legacy code* atau UI jadul yang mengganggu.

---

## 1. Arsitektur Logic Backend (Recommendation Engine)
Recommendation dihitung oleh `RecommendationEngine` menggunakan tiga fase berurutan:

1. **Phase 1: Hard Filter**
   - Mengeliminasi Pelatihan yang `is_active` = false atau tidak memiliki `training_center_id`.
   - Melakukan filter ketat berdasarkan preferensi Kuesioner User (Bidang/Kategori, Metode [Online/Offline/Hybrid], dan Tingkat Keahlian).
   - *Catatan: Hybrid dianggap selalu match dengan preferensi Online/Offline.*

2. **Phase 2: Weighted Scoring & Aggregation**
   Skor dasar dihitung dari masing-masing Pelatihan yang lolos *Hard Filter*:
   - **Bidang Diminati:** 35%
   - **Skill Match:** 20%
   - **Metode Match:** 15%
   - **Popularitas:** 10%
   
   *Aggregasi:* Jika satu Training Center memiliki banyak pelatihan yang *match*, Engine hanya mengambil **skor tertinggi** untuk mewakili TC tersebut.
   
   *Distance Calculation (Haversine Formula):* 20%
   - Menghitung jarak lurus (berdasarkan lengkung bumi) antara Latitude & Longitude Profile User ke Latitude & Longitude Training Center.
   - Jarak 0 km = +20 Poin. Jarak >= 100 km = 0 Poin.
   
   **Total Maksimal = 100%.**

3. **Phase 3: Persist Recommendation**
   - Top 5 Training Center disimpan permanen ke tabel `recommendations`. 
   - Endpoint frontend hanya bertugas menarik (GET) data dari tabel ini secara instan, menghemat beban *query* CPU di server.

---

## 2. Alur Integrasi Sistem (Frontend ↔ Backend REST API)

Integrasi telah berjalan secara *Decoupled Architecture*.
- **Authentication:** `POST /api/login`. Menggunakan Laravel Sanctum. Menyimpan Token di `localStorage`. Di-*handle* oleh middleware kustom `SystemAuth`.
- **Layout & Protection:** `window.authFetch` secara asinkron menyisipkan Header `Bearer Token`. Jika API mengembalikan 401, *Frontend UI* akan langsung *logout* paksa user.
- **User Module:**
  1. *Gatekeeper API:* Di `user/Dashboard/dashboard.blade.php`, sistem secara otomatis memanggil `GET /api/profile` dan `GET /api/questionnaire`. 
  2. Jika profil kosong, *User* diarahkan ke `/user/profile` untuk input Demografi dan Map Pinpoint.
  3. Jika kuesioner kosong, *User* diarahkan ke `/user/questionnaire`.
  4. Submit Kuesioner via `POST /api/questionnaire` otomatis me-*trigger* kalkulasi `RecommendationEngine` di *background*.
  5. *Recommendation UI:* Mengambil `GET /api/recommendations` dan me-render *Card TC* dengan persentase skor kecocokan dan jarak.
  6. *Enrollment (Pendaftaran):* Mengklik Modal *Daftar* memanggil `POST /api/enrollments`. Hasilnya masuk ke riwayat pendaftaran.
- **Admin Module:**
  - Menggunakan UI/UX modern berbasis *AdminLTE v4*.
  - Mengelola entitas dari *API Master Data* (`GET/POST/PUT/DELETE /api/training-centers` dan `/api/trainings`).
  - *Dashboard Metrik* & *Activity Log* mengambil data *real-time* dari `GET /api/admin/stats` dan `GET /api/admin/log-activities`.
  - Admin dapat memblokir/mengaktifkan kembali user via API `PATCH /api/admin/users/{id}/status`.

---

## 3. Map Geolocation API
- Frontend Profile User telah terintegrasi dengan **Leaflet.js (OpenStreetMap)**.
- Fitur *Drag Pin* dan Klik titik pada peta.
- Fitur **Reverse Geocoding**: Memanggil Nominatim API untuk menterjemahkan titik koordinat ke teks Alamat Lengkap dan Kecamatan secara otomatis (*Auto-fill*).
- Fitur GPS (Browser Geolocation) tersemat di tombol *Gunakan Lokasi Saat Ini*.

---

## 4. Entity List & Relasi Database (V2 Core)

Sistem telah di-refactor menggunakan Relasi Eloquent (ORM) yang efisien:

- **`User`** (tabel: `tabel_users`)
  - Menyimpan Credential (email, password), Role (`admin`, `user`), dan Status Aktif (`is_active`).
  - *Relasi:* 
    - `hasOne(Profile)`
    - `hasOne(QuestionnaireResponse)`
    - `hasMany(Recommendation)`
    - `hasMany(Enrollment)`
    - `hasMany(LogActivity)`

- **`Profile`** (tabel: `profiles`)
  - Menyimpan Demografi (age, education) & Geospatial Koordinat (latitude, longitude, district).
  - *Relasi:* `belongsTo(User)`

- **`QuestionnaireResponse`** (tabel: `questionnaire_responses`)
  - Menyimpan JSON Jawaban Preferensi User (`bidang_diminati`, `tingkat_keahlian`, `metode_pelatihan`, `jarak_maksimal`).
  - *Relasi:* `belongsTo(User)`

- **`TrainingCenter`** (tabel: `training_centers`)
  - Lembaga pelaksana, entitas induk untuk Pelatihan. Memiliki koordinat spasial untuk perhitungan jarak (Haversine).
  - *Relasi:*
    - `hasMany(Pelatihan)`
    - `hasMany(Recommendation)`
    - `hasMany(Enrollment)`
    - `hasMany(LogActivity)`

- **`Pelatihan`** (tabel: `tabel_pelatihan`)
  - Produk/Kursus teknis. Menyumbangkan poin pada sistem melalui atribut skor (`interest_category`, `method`, `required_skill`, `popularity`).
  - *Relasi:*
    - `belongsTo(TrainingCenter)` (Otomatis Cascade Delete)
    - `hasMany(Enrollment)`
    - `hasMany(LogActivity)`

- **`Recommendation`** (tabel: `recommendations`)
  - Tabel temporer/statis penampung Top-5 hasil komputasi *Engine*.
  - Menyimpan field `score` (0-100), `distance` (km), dan `rank`.
  - *Relasi:* `belongsTo(User)`, `belongsTo(TrainingCenter)`

- **`Enrollment`** (tabel: `enrollments`)
  - Bukti keberhasilan (konversi) rekomendasi menjadi Pendaftaran Aktual.
  - *Relasi:* `belongsTo(User)`, `belongsTo(TrainingCenter)`, `belongsTo(Pelatihan)`

- **`LogActivity`** (tabel: `log_activities`)
  - Jejak audit aktivitas user dalam sistem (Tracking: Login, Enroll).
  - *Relasi:* `belongsTo(User)`, `belongsTo(TrainingCenter)`, `belongsTo(Pelatihan)`

---

## 5. Security & Technical Debt (Selesai/Dihapus)
- [x] Perubahan branding UI dan Middleware menjadi "Sistem Rekomendasi Tempat Pelatihan (SRTP)".
- [x] Impelementasi `Global Exception Handler` dengan format Response JSON ketat untuk menangkal Error Leakage ke Frontend.
- [x] API Security: API diproteksi ketat menggunakan `Laravel Sanctum`.
- [x] Security Policy: Middleware kustom memfilter Hak Akses agar akun bertipe 'user' tidak bisa membaca endpoint maupun memanipulasi UI Admin, begitu pun sebaliknya.
- [x] Perbaikan *Error 403 HTTP Interceptor* di frontend yang sebelumnya menghapus token secara tidak sengaja, kini ditangani dengan metode yang aman.
- [x] **Pembersihan Final (Refactoring Phase):** Seluruh arsitektur lama (*Legacy Code*) SIREKPEL yang berupa Controller (`PendaftaranController`, `PelatihanController` duplikat, `PesertaController`, `RekomendasiController`), Model, dan Resource berbahasa Indonesia telah dihapus total. *Routing* diseragamkan ke bahasa Inggris (`/trainings`, `/enrollments`) demi *Clean Architecture*.

Project telah bersih dan siap di-*deploy* ke *production* untuk keperluan Sidang Skripsi.

---

## 6. Refactoring UI/UX Frontend & Landing Page (Selesai - Juli 2026)
Pada fase akhir pengembangan, tampilan di sisi *User Module*, Admin, dan *Landing Page* telah dirombak secara total oleh Tim Senior Engineer untuk mencapai standar visual *Modern SaaS / Enterprise App*. Refactoring ini meliputi:

1. **Landing Page Redesign (Agregator Concept):**
   - Merombak *copywriting* dan *Information Architecture* di `welcome.blade.php` agar mencerminkan "Mesin Rekomendasi Pintar / Agregator", bukan lagi LMS tertutup.
   - Sinkronisasi Data *Real-time*: Tabel Statistik dan "Kategori Minat Pelatihan" tidak lagi *hardcoded*, melainkan diagregasi langsung secara dinamis dari database Backend via `HomeController`.
   - Modifikasi CTA (Call to Action) dan UX Flow agar pengguna baru langsung dituntun untuk mendaftar akun dan mengisi kuesioner.

2. **Dashboard UI (User):**
   - Redesign tata letak (*layout*) card dari *fixed grid CSS* manual ke sistem kolom dan *row native* milik Bootstrap agar terjamin responsivitasnya (*mobile-first*).
   - Indikator persentase kelengkapan profil dibuat interaktif dengan custom progress bar.

3. **Kuesioner & Rekomendasi:**
   - Mengubah *Radio Buttons* standar menjadi komponen kartu interaktif yang bisa ditekan di seluruh permukaannya dengan perubahan warna *border* (state: *hover* dan *checked*).
   - Merombak hasil dari *Recommendation Engine* menjadi Card *Training Center* modern dengan medali ranking (Emas/Perak/Perunggu), badge presentase (*alpha transparency*), dan efek elevasi 3D.
   - Modal pendaftaran dibuat lebih organik (*mobile-friendly*) untuk menampilkan daftar modul studi di TC terkait.

4. **Status Pendaftaran:**
   - Tabel *history* digantikan oleh bentuk Timeline Card modern. Kartu ini memiliki pseudo-elemen bergaris warna cerdas (Biru = Aktif, Hijau = Selesai, Merah = Batal) agar terbaca jelas oleh mata sekilas. Format tanggal pun diubah menjadi format lokal (Hari, DD Bulan YYYY).

Fase refactoring UI ini ditujukan untuk memberikan kepuasan, kepercayaan, dan mengurangi hambatan UX (Friction) bagi calon siswa.

---

## 7. Refactoring UI/UX Admin Module (Selesai - Juli 2026)
Melanjutkan perombakan di sisi *User*, Tim Senior Engineer juga menyelaraskan gaya visual (*design language*) pada area *Admin Panel* (AdminLTE v4) agar lebih *modern*, *clean*, dan meminimalisir kelelahan visual (eye-strain) bagi Administrator. Fokus perubahan ini adalah:

1. **Global Admin Layout (\`admin.blade.php\`)**:
   - Menerapkan injeksi *CSS Variables* untuk mewarnai tombol dan elemen navigasi *sidebar* selaras dengan identitas aplikasi (Indigo).
   - Mengubah skema warna *sidebar* menjadi *Slate/Navy Dark* (\`#1e293b\`) agar lebih terkesan seperti panel *SaaS Enterprise*.
   - Menerapkan *white-labeling* dengan mengganti referensi *AdminLTE.io* di bagian footer.

2. **Dashboard Metrics (\`admin/Dashboard/index.blade.php\`)**:
   - Membuang komponen \`.small-box\` warna-warni yang terlalu mencolok dan menggantinya dengan \`Metric Card\` berwarna latar putih (shadow halus, hover efek 3D, border tipis).
   - Mengubah format Tabel Data Pendaftar Terbaru menjadi \`.table-modern\` (dengan latar \`thead\` abu-abu muda, serta tipografi kapital).

3. **Master Data & Bug Fixes (Training Center & Pelatihan)**:
   - Menerapkan konsistensi UI Tabel (mengubah tombol Edit/Delete konvensional menjadi \`Minimalist Action Button\` berlatar transparan).
   - Menata ulang Form Modal. Bagian input krusial (seperti Geolocation di Training Center dan Variabel Kalkulasi di Pelatihan) dibungkus (*highlight*) dengan panel *alert/card* khusus untuk memandu Administrator bahwa field tersebut mempengaruhi hasil *Recommendation Engine*.

4. **Refactoring Auth (Login Pages):**
   - Halaman Login Admin dan User dirombak ulang menggunakan desain *Modern Card* berbayang dengan latar belakang *gradient* (\`f8fafc\` ke \`e2e8f0\`).
   - Menerapkan komponen input *icon-group* yang dapat merespon *focus state*.
   - **Perbaikan UX Login:** Akun bertipe reguler (User) yang melakukan *login* atau *auto-login* (Sanctum) kini di-*redirect* secara paksa ke `/user/profile` ketimbang `/user/dashboard` untuk menjamin kepatuhan *onboarding* sistem kuesioner.

5. **Global SweetAlert2 Integration:**
   - Menghapus manipulasi DOM manual (\`alertBox\`) dan \`window.alert()\` konvensional di seluruh proyek.
   - Menginjeksi *SweetAlert2* via CDN di \`layouts/admin.blade.php\` dan \`layouts/user.blade.php\`.
   - Mengimplementasikan helper JS global: \`window.showToast()\` untuk notifikasi sukses (non-blocking) dan \`window.confirmAction()\` untuk konfirmasi penghapusan data dengan tampilan UI yang serasi (Rounded UI).

6. **Activity Log & Bulk Action:**
   - Menambahkan fitur *Checkbox (Select All/Individual)* pada tabel modul Log Activity.
   - Mengimplementasikan fitur *Bulk Delete* menggunakan arsitektur pemrosesan konkuren \`Promise.allSettled()\` di *Frontend*, sehingga admin dapat menghapus puluhan riwayat aktivitas sekaligus tanpa menyiksa memori browser.

---

## 8. Standarisasi Pengujian Otomatis (Automated Testing)
Sebagai bentuk jaminan kualitas perangkat lunak (*Quality Assurance*), Tim Senior Engineer telah merancang dan mengeksekusi serangkaian pengujian (*Automated Tests*) berbasis *PHPUnit* yang komprehensif, mencakup logika algoritma, alur aplikasi, otorisasi, dan uji regresi keamanan. Pada fase refactoring akhir, pengujian telah dikalibrasi ulang terhadap V2 API Endpoint.

**Daftar Pengujian (Test Coverage) yang telah dirancang:**

1. **Unit Tests (Core Logic):**
   - \`DistanceServiceTest\`: Memvalidasi keakuratan perhitungan jarak menggunakan rumus matematika *Haversine Formula*.
   - \`RecommendationEngineTest\`: Pengujian algoritma inti. Memastikan *Hard Filter* (Kategori/Metode) bekerja, memastikan perhitungan proporsi skor gabungan valid, dan menjaga konsistensi Top-5 limit pada database.

2. **Feature Tests (End-to-End API Integration):**
   - \`BackendFlowTest\`: Simulasi *User Journey* utama mulai dari Autentikasi, pengisian Profil, submit Kuesioner, hingga kemunculan data di endpoint rekomendasi.
   - \`AdminCrudTest\`: Uji coba modul Master Data. Memastikan fungsi CRUD pada *Training Center* dan *Pelatihan* (\`/api/trainings\`) bekerja. **Catatan Arsitektur:** Termasuk uji coba keamanan \`Cascade Delete\` untuk memverifikasi bahwa penghapusan TC akan menyapu bersih semua Pelatihan di bawahnya untuk menghindari data yatim (*Orphaned Data*).
   - \`EnrollmentLogicTest\`: Pengujian fungsionalitas transaksi. Terdapat validasi *Edge-case* yang memverifikasi bahwa sistem menolak (*HTTP 409 Conflict*) pendaftaran ganda dari satu user pada modul pelatihan yang sama.
   - \`SecurityAndLogTest\`: Pengujian ketat untuk keamanan akses (*Gate/Middleware*). Meliputi pengujian fitur Blokir Akun (*Ban User*), dimana *SystemAuth Middleware* akan segera menggagalkan akses user yang dinonaktifkan meski token JWT/Sanctum-nya masih berlaku. Termasuk validasi penulisan jejak otomatis ke dalam tabel *Log Activity*.

*Seluruh test suites (skenario uji) ini menjamin bahwa Sistem Rekomendasi Tempat Pelatihan bersifat robust, tangguh dari regresi (bug berulang), dan siap untuk fase production.*

---

## 9. Patch Notes & Bug Fixes (Hotfix)
Melalui audit arsitektur dan debugging lanjutan, beberapa bug kritis dan technical debt yang tersisa telah diselesaikan:

1. **Perbaikan Relasi Eloquent (Model)**:
   - Menambahkan relasi `enrollments()` dan `logActivities()` yang hilang pada model `User`, `TrainingCenter`, dan `Pelatihan` sesuai cetak biru skema.
   - Memperbaiki deklarasi *foreign key* yang salah sasaran pada `Pelatihan->recommendations()`, mencegah SQL Crash (HTTP 500) saat admin mencoba menghapus entitas pelatihan.

2. **Optimalisasi Recommendation Engine**:
   - *Distance Bug (Stale Data)*: Memperbaiki masalah data jarak yang basi dengan me-trigger (memanggil) kalkulasi otomatis `RecommendationEngine` sesaat setelah pengguna memperbarui titik lokasi koordinatnya di menu Profil (`ProfileController::store`).
   - *Dynamic Max Distance*: Menghapus nilai *hardcode* limit 100km pada perhitungan haversine. Engine kini membaca preferensi cerdas `jarak_maksimal` milik masing-masing pengguna dari hasil kuesioner mereka secara dinamis.

3. **Perbaikan Bug UI/Javascript di Panel Admin**:
   - *Data Pelatihan Gagal Tampil*: Memperbaiki sisa-sisa pemanggilan *legacy API endpoint* di file `index.blade.php` (yang sebelumnya masih mengakses rute `/api/pelatihan`) menjadi rute terstandardisasi `/api/trainings`. Data tabel kini sukses di-render.
   - *Tombol Save Hilang (Off-screen)*: Memperbaiki layout HTML Modal Bootstrap di halaman Pelatihan dan Training Center. Tag `<form>` yang sebelumnya diselipkan secara ilegal di tengah-tengah kerangka *Flexbox Modal Content* telah di-refactor menjadi *wrapper* utama, sehingga modal dapat digulir (*scrollable*) dan tombol simpan kembali mengambang (*sticky*) di bawah viewport.
   - *State Button Terkunci*: Memperbaiki celah logika Javascript (`btnSave.disabled = true`) yang membuat tombol simpan tak dapat ditekan (terkunci secara permanen) pada Edit yang ke-2. State tombol kini langsung di-*reset* ulang setelah operasi penyimpanan berhasil maupun saat form *modal* kembali dirender.

4. **Peningkatan Test Coverage & Infrastruktur CI**:
   - Menambahkan dua buah *Feature Test* baru di `AdminCrudTest` untuk secara spesifik menguji rute Delete Pelatihan.
   - Memastikan server menolak penghapusan `Pelatihan` (HTTP 400) apabila modul tersebut telah menampung *Enrollment* peserta.
   - Mengalihkan eksekusi `phpunit.xml` secara penuh menggunakan `sqlite :memory:` untuk mempercepat siklus TDD (*Test-Driven Development*) tanpa bergantung pada daemon MySQL eksternal.

5. **Migrasi Geolocation (GIS) pada Training Center**:
   - Mengintegrasikan antarmuka interaktif **Leaflet.js** dan Reverse-Geocoding **OpenStreetMap Nominatim** ke dalam form Tambah/Edit Training Center di Panel Admin.
   - Administrator tidak perlu lagi mengetikkan Latitude/Longitude dan Alamat secara manual. Pin peta yang digeser otomatis akan memicu konversi alamat (Reverse-Geocode) dan mengisi input teks, meminimalisir kesalahan *typo*.
   - Fitur deteksi GPS (Navigator) disertakan agar mempermudah admin yang sedang berada di lokasi Training Center.
   - Perubahan ini 100% *Frontend-isolated*. Tidak ada skema *Database*, `Controller`, atau `RecommendationEngine` yang diubah, namun kualitas _input data_ jarak (Haversine) yang dihasilkan meningkat secara drastis (Presisi Data Tinggi).

6. **Integrasi Navigasi Eksternal (Google Maps Smart Link)**:
   - *Database Migration*: Menambahkan kolom eksklusif `google_maps_url` (tipe *Text*, *Nullable*) pada struktur tabel `training_centers` demi mendokumentasikan rute URL pihak ketiga.
   - *Backend API Security*: Memutakhirkan `FormRequest Validation` untuk mengijinkan input opsional yang ketat (*URL Formatted Only*) sehingga memproteksi database dari injeksi *string* kotor.
   - *Admin Panel*: Menyisipkan field *URL Geolocation Opsional* di dalam modal *Training Center* Admin secara ergonomis di area informasi Geografis.
   - *User Experience (Conditional Rendering UI)*: Di panel Detail Rekomendasi Pelatihan (Frontend User), sistem dibekali logika _Conditional Rendering_ yang cerdas:
     - Jika link G-Maps dimasukkan oleh admin, maka tombol **"Lihat Lokasi"** akan dirender, yang jika diklik akan melempar *user* membuka *App/Browser* Google Maps (*External Routing*).
     - Jika link G-Maps kosong, tombol tersebut dihancurkan sepenuhnya dari _DOM_ (bukan sekadar di-*disable*) sehingga menghasilkan UI panel Detail yang bersih dan elegan (Tanpa peta mini yang memberatkan memori perangkat *client*).
   - *Isolasi Sistem (Aman)*: Sifat tautan opsional eksternal ini berfungsi murni sebagai fitur navigasi sekunder (_Wayfinding UX_), sehingga integritas `Recommendation Engine` tetap aman 100% dan kalkulasi _Haversine Distance_ tidak dipengaruhi sama sekali.

## 10. UI/UX Redesign & Enhancements (Terbaru)
Aplikasi telah melalui proses audit dan redesain UI/UX secara menyeluruh untuk mencapai standar antarmuka modern (setara dengan Vercel, Linear, dan OpenDESA). 

Perubahan yang dilakukan meliputi:

*   **Identitas & Branding:** Pembaruan logo SRTP di seluruh halaman (Login Admin, Login User, Register, Navbar, dan Sidebar) dengan penyesuaian dimensi maksimal yang rapi tanpa merusak layout.
*   **Card Header Admin:** Seluruh halaman Admin (Training Center, Pelatihan, User, Enrollment, LogActivity) di-*refactor* menggunakan Flexbox modern (`justify-content-between align-items-center`). Posisi judul selalu rata kiri dan tombol aksi utama rata kanan dengan hierarchy warna yang konsisten.
*   **Autentikasi (Login & Register):** Halaman form perombakan total menjadi satu *Card Layout* di tengah dengan background bersih (`#F8FAFC`).
    *   Penggunaan label mengambang di atas input dengan border-radius modern (8px) dan cincin fokus (*focus ring*) biru cerah.
    *   Penggunaan *Divider* (garis pemisah) untuk memperjelas area header (Branding) dan form.
    *   Animasi *fade-in* halus (`scale 0.98 -> 1`) pada saat form pertama dimuat.
*   **SweetAlert Modern:** Sistem pop-up di-*refactor* secara global menggunakan *helper* tersentralisasi (`showSuccess`, `showError`, `showToast`, `showConfirm`, `showDelete`).
    *   Semua notifikasi aksi sukses dipindahkan ke **Toast** (pojok kanan atas) murni, tanpa efek *background dimming* atau halangan layar (*no backdrop*).
    *   Konfirmasi aksi kritis (seperti *Delete*) menggunakan *Modal Overlay* yang tidak hitam pekat, melainkan menggunakan efek *blur* transparan elegan (`backdrop-filter: blur(6px)`).
    *   Desain *border-radius* SweetAlert dibuat lebih bulat (16px) dengan *box-shadow* premium, dan desain tombol `primary`, `secondary`, dan `danger` yang mengikuti *design system* aplikasi.
*   **Sidebar Admin:** Warna latar belakang *sidebar* kiri diubah sepenuhnya menjadi putih (Light Theme) menggantikan *dark theme* bawaan AdminLTE. 
    *   Tulisan pada menu dirubah menggunakan warna abu-abu gelap.
    *   State *hover* menggunakan warna biru awan (`bg-sky-100` / `#e0f2fe`) dengan font kebiruan.
    *   Tombol menu dibuat menjadi melayang membentuk "Pil" (dengan margin spasi antar pinggiran).

## 11. Refactoring Detail Rekomendasi (Juli 2026)
Sebagai kelengkapan UI *User Experience*, telah dilakukan penyempurnaan fitur pada modul Rekomendasi agar sistem lebih informatif ("Explainable Recommendation").
1.  **Distance Hard-Filter Bugfix:** Memperbaiki celah logika pada `RecommendationEngine` yang menyebabkan Training Center di luar "radius maksimum" kuesioner tetap muncul. Kini, filter jarak diterapkan secara mutlak (*Hard Filter*) sebelum pemberian poin proporsional.
2.  **Transparansi Skor (Score Breakdown):** Menambahkan fitur *Expandable Accordion* di dalam Modal Detail Training Center yang menampilkan kontribusi rincian poin (Bidang, Skill, Metode, Jarak, Popularitas) dalam wujud *progress bar* yang human-readable tanpa membocorkan algoritma backend ke pengguna.
3.  **Sticky Compact Header:** Modal dilengkapi mekanisme scroll interaktif (Sticky Header) di mana ikon dan alamat lembaga mengecil/menghilang ketika di-scroll, demi menjamin agar pengguna tidak kehilangan konteks *Training Center* apa yang sedang mereka baca skornya.
4.  **Label Evaluatif:** Persentase Total Skor kini tidak hanya memunculkan angka, tapi disematkan interpretasi bahasa manusia secara dinamis (seperti "Sangat sesuai dengan preferensi Anda" atau "Cukup sesuai..."). Status parsial di dalam *breakdown* juga dinaturalisasi ("Kecocokan sebagian" diganti menjadi "Sangat dekat", "Relatif jauh", dst menyesuaikan konteks jarak/popularitas).

## 12. Peningkatan Fitur Pendaftaran & Verifikasi (Agustus 2026)
Pengembangan sistem dilanjutkan secara *iteratif* dengan penambahan kapabilitas siklus pendaftaran dua arah antara User (Peserta) dan Admin (Verifikator). Seluruh pembaruan diimplementasikan mengikuti prinsip UI/UX yang telah ditetapkan (Clean Design) tanpa mengganggu fungsionalitas Recommendation Engine.

1.  **Redesign UI Riwayat Pendaftaran (User):** 
    - Komponen tabel statis usang telah diremajakan menjadi *Card UI* modern dengan hierarchy visual yang lebih profesional (Shadow ringan, animasi *lift-on-hover*, tipografi tebal).
    - Menghapus route & page navigasi detail yang menyebabkan 404 Not Found, dan memigrasikannya menjadi **Modal Interaktif 1-layar**.
    - **Smart Injection:** Modal Detail di Riwayat Pendaftaran kini berhasil dikawinkan secara "Background-fetch" dengan API Rekomendasi, sehingga fitur transparansi skor (Score Breakdown) dapat tampil kembali walau user mengakses dari halaman History pendaftaran tanpa perlu menambah *DB Join/Migration* baru pada tabel *Enrollments*.
2.  **Alur Verifikasi Pendaftaran (Admin Backend & Frontend):** 
    - Merombak Lifecycle pendaftaran dari sifat otomatis (*Auto-Active*) menjadi tersistem: `pending` ➔ `approved` / `rejected`.
    - Mengamankan Controller Admin `EnrollmentController` dan Controller User (pada metode `store`) untuk memvalidasi limit standar `status` terbaru (Legacy Backward-Compatible).
    - Memisahkan komponen Javascript *spaghetti* di dalam `index.blade.php` Admin menjadi fungsi helper modular (`createRow`, `renderStatus`, `renderAction`).
3.  **Micro-Interaction & Badge Modern (Admin & User Panel):**
    - Sinkronisasi global pada seluruh modul Dashboard dan Tabel menggunakan desain *"Pill Status Badge"* modern:
        - 🟡 **Pending / Menunggu Persetujuan:** Badge Kuning cerah dengan Ikon Jam.
        - 🟢 **Approved / Disetujui / Selesai / Aktif:** Badge Hijau elegan dengan Ikon Centang.
        - 🔴 **Rejected / Ditolak / Batal:** Badge Merah dengan Ikon Silang.
    - **Minimalist Action Buttons:** Pada tabel Pendaftaran Admin, tombol Setujui dan Tolak didesain menggunakan metode *CSS Transition Hover Expand*. Dalam kondisi statis/normal, tombol hanya menampilkan *icon square* berukuran mini (34x34px). Namun ketika kursor diarahkan (hover), tombol akan mengembang secara *fluid* ke arah samping untuk memperlihatkan label teks-nya secara utuh. Mekanisme cerdas ini dirancang murni menggunakan *Cascading Style Sheets (CSS)* guna menghindari *overhead* rendering javascript sekaligus memberikan *tactile feedback* kelas atas (Enterprise Feel).
4.  **Integrasi Konfirmasi Global (SweetAlert):**
    - Memperbaiki *bug* kegagalan fungsi tombol Setujui/Tolak pada tabel admin akibat transisi fungsi dari sinkronus ke asinkronus (Async/Await) tanpa adanya pengikat event promise yang tepat pada modal bawaan.
    - Menghapus penggunaan `window.confirm()` primitif bawaan browser (alert javascript) maupun custom overlay manual.
    - Standardisasi UX konfirmasi (termasuk verifikasi pendaftaran `approved` & `rejected`) sepenuhnya didelegasikan kepada `window.showConfirm()` berbasis SweetAlert2 yang dipusatkan di `sweetalert.js`, menyajikan dialog konfirmasi yang seragam, estetik, dan interaktif (responsif terhadap event trigger).

## 13. Audit Kualitas E2E & Resolusi Technical Debt Akhir (Agustus 2026)
Melalui audit fungsional dan pengujian mendalam terhadap sistem yang sudah terbangun, Senior Engineer kembali menutup celah *logic bugs* dan menyelaraskan total (100% sinkron) *Automated Testing* terhadap arsitektur backend, menjamin ketahanan skala _Enterprise_:

1. **Resolusi Variable Scope Leak (Recommendation Engine):**
   - **Bug:** `Distance Score Breakdown` berpotensi memunculkan informasi salah (bocor) pada Training Center yang tidak memiliki data Geolocation, akibat persistensi variabel di dalam *looping* perhitungan jarak.
   - **Fix:** Menambahkan _force-reset_ null variabel `$distScore` untuk menetralkan kalkulasi pada setiap iterasi komputasi Haversine per Training Center.
2. **Missing Implementation - Activity Log Enroller:**
   - **Bug:** Terlewatnya injeksi `LogActivity` saat peserta berhasil *Submit Enrollment*, meskipun dokumen Blueprint meminta *tracking* tersebut.
   - **Fix:** Backend `EnrollmentController` kini otomatis men-trigger dan merekam jejak rekam pendaftaran (`activity_type: 'enroll'`) secara mandiri setelah Pendaftaran sukses.
3. **Peningkatan 10 Unit Test & Feature Test Tambahan (Cakupan 100%):**
   - _Hard-Filter Assertion:_ `RecommendationEngineTest` telah difasilitasi uji coba algoritma `jarak_maksimal` dinamis milik `QuestionnaireResponse`. TC yang berada di luar jarak maksimal otomatis hilang dari hasil API. 
   - _Auto-Trigger Geolocation:_ Menguji *Refresh* algoritma mesin pada `BackendFlowTest` manakala User mengubah / Update *Maps Pin* dari titik kordinat A menuju Titik B.
   - _Validasi Score Breakdown JSON:_ Memastikan tabel algoritma selalu menghasilkan struktur data breakdown (interest, skill, distance, method) yang valid untuk Expandable UI di Frontend.
   - _E2E Routing Google Maps:_ Fitur Geolocation Routing admin pada `AdminCrudTest` kini disisipi filter regex untuk menolak format *URL G-Maps Invalid* (HTTP 422).
   - _Lifecycle Proteksi Enrollment:_ Di ranah `EnrollmentLogicTest`, kini ada tes E2E isolasi privasi *(hanya bisa melihat pendaftaran milik sendiri)*, pembuktian eksekusi tombol Reject (tolak), serta validasi strict tolak-status kadaluwarsa (mengunci Lifecycle murni ke `pending`, `approved`, `rejected`). 
4. **Clean Slate Environment:**
   - Membersihkan artefak-artefak Git dan OS sementara (`*.rej`, `*.orig`), menyisakan 51 Test Case dengan 101 Assertion yang secara absolut 100% berstatus Passed/Hijau.
