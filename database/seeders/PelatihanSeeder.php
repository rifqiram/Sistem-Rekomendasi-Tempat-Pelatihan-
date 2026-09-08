<?php

namespace Database\Seeders;

use App\Models\Pelatihan;
use Illuminate\Database\Seeder;

class PelatihanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pelatihan::insert([
            [
                'judul' => 'Fundamental Web Development dengan Laravel',
                'deskripsi' => 'Dasar-dasar pengembangan web menggunakan Laravel untuk membangun aplikasi CRUD dan MVC.',
                'kategori' => 'Web Development',
                'interest_category' => 'IT',
                'method' => 'Hybrid',
                'required_skill' => 'Beginner',
                'level' => 'Beginner',
                'durasi' => '30 Jam',
                'sertifikat' => 'Ya',
                'training_center_id' => 1, // Asumsi 1 = BLK Magetan
                'priority' => 5,
                'tanggal_mulai' => '2026-06-01',
                'tanggal_selesai' => '2026-06-08',
                'is_active' => true,
                'status' => 'Aktif',
            ],
            [
                'judul' => 'Backend API Development Menggunakan Laravel REST API',
                'deskripsi' => 'Membangun REST API yang scalable dan aman dengan Laravel untuk kebutuhan backend modern.',
                'kategori' => 'Backend Development',
                'interest_category' => 'IT',
                'method' => 'Online',
                'required_skill' => 'Intermediate',
                'level' => 'Intermediate',
                'durasi' => '36 Jam',
                'sertifikat' => 'Ya',
                'training_center_id' => 1,
                'priority' => 4,
                'tanggal_mulai' => '2026-06-10',
                'tanggal_selesai' => '2026-06-16',
                'is_active' => true,
                'status' => 'Aktif',
            ],
            [
                'judul' => 'Digital Marketing dan Social Media Strategy',
                'deskripsi' => 'Strategi pemasaran digital dan optimasi kampanye di media sosial.',
                'kategori' => 'Digital Marketing',
                'interest_category' => 'Bisnis',
                'method' => 'Offline',
                'required_skill' => 'Beginner',
                'level' => 'Beginner',
                'durasi' => '24 Jam',
                'sertifikat' => 'Ya',
                'training_center_id' => 2, // Asumsi 2 = BLK Madiun
                'priority' => 3,
                'tanggal_mulai' => '2026-06-18',
                'tanggal_selesai' => '2026-06-22',
                'is_active' => true,
                'status' => 'Aktif',
            ],
            [
                'judul' => 'Administrasi Perkantoran Modern',
                'deskripsi' => 'Manajemen dan administrasi perkantoran menggunakan tools digital.',
                'kategori' => 'Administrasi',
                'interest_category' => 'Bisnis',
                'method' => 'Offline',
                'required_skill' => 'Beginner',
                'level' => 'Beginner',
                'durasi' => '20 Jam',
                'sertifikat' => 'Ya',
                'training_center_id' => 2,
                'priority' => 2,
                'tanggal_mulai' => '2026-06-24',
                'tanggal_selesai' => '2026-06-27',
                'is_active' => true,
                'status' => 'Aktif',
            ],
            [
                'judul' => 'Bahasa Jepang Dasar (N5)',
                'deskripsi' => 'Pengenalan dan pelatihan intensif Bahasa Jepang level dasar N5.',
                'kategori' => 'Bahasa',
                'interest_category' => 'Pendidikan',
                'method' => 'Offline',
                'required_skill' => 'Beginner',
                'level' => 'Beginner',
                'durasi' => '40 Jam',
                'sertifikat' => 'Ya',
                'training_center_id' => 3, // Asumsi 3 = LPK Sakura
                'priority' => 5,
                'tanggal_mulai' => '2026-07-01',
                'tanggal_selesai' => '2026-07-06',
                'is_active' => true,
                'status' => 'Aktif',
            ],
            [
                'judul' => 'Persiapan Ujian Tokutei Ginou Kaigo',
                'deskripsi' => 'Pelatihan intensif careworker (Kaigo) untuk persiapan tes skill worker ke Jepang.',
                'kategori' => 'Keperawatan',
                'interest_category' => 'Kesehatan',
                'method' => 'Hybrid',
                'required_skill' => 'Intermediate',
                'level' => 'Intermediate',
                'durasi' => '32 Jam',
                'sertifikat' => 'Ya',
                'training_center_id' => 3,
                'priority' => 5,
                'tanggal_mulai' => '2026-07-08',
                'tanggal_selesai' => '2026-07-12',
                'is_active' => true,
                'status' => 'Aktif',
            ],
        ]);
    }
}
