<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\QuestionnaireResponse;
use App\Models\TrainingCenter;
use App\Models\Pelatihan;
use Illuminate\Database\Seeder;

class RecommendationScenarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Setup Training Center Dummy
        $tcNear = TrainingCenter::firstOrCreate(
            ['nama' => 'TC Magetan IT Center'],
            [
                'alamat' => 'Magetan Kota',
                'latitude' => -7.6500, // Dekat dengan User
                'longitude' => 111.3300,
                'telepon' => '081111111',
                'status' => 'active'
            ]
        );

        $tcFar = TrainingCenter::firstOrCreate(
            ['nama' => 'TC Surabaya Tech'],
            [
                'alamat' => 'Surabaya',
                'latitude' => -7.2500, // Jauh dari User (+100km)
                'longitude' => 112.7500,
                'telepon' => '082222222',
                'status' => 'active'
            ]
        );

        // 2. Setup Dummy Pelatihan untuk TC
        Pelatihan::firstOrCreate(
            ['judul' => 'IT Training Near'],
            [
                'training_center_id' => $tcNear->id,
                'interest_category' => 'IT',
                'method' => 'Hybrid',
                'required_skill' => 'Beginner',
                'priority' => 5,
                'tanggal_mulai' => '2026-08-01',
                'tanggal_selesai' => '2026-08-10',
                'is_active' => true,
            ]
        );

        Pelatihan::firstOrCreate(
            ['judul' => 'IT Training Far'],
            [
                'training_center_id' => $tcFar->id,
                'interest_category' => 'IT',
                'method' => 'Hybrid',
                'required_skill' => 'Beginner',
                'priority' => 5,
                'tanggal_mulai' => '2026-08-01',
                'tanggal_selesai' => '2026-08-10',
                'is_active' => true,
            ]
        );

        // 3. Buat User Tester
        $userTester = User::firstOrCreate(
            ['email' => 'tester@example.com'],
            [
                'name' => 'Tester User',
                'password' => bcrypt('password'),
                'role' => 'user',
                'api_token' => 'tester-token',
                'is_active' => true,
            ]
        );

        // Profile User (Magetan Area)
        Profile::updateOrCreate(
            ['user_id' => $userTester->id],
            [
                'age' => 20,
                'latitude' => -7.6400,
                'longitude' => 111.3200,
            ]
        );

        // Questionnaire (Minat IT, Beginner, Hybrid)
        QuestionnaireResponse::updateOrCreate(
            ['user_id' => $userTester->id],
            [
                'answers' => json_encode([
                    'bidang_diminati' => 'IT',
                    'tingkat_keahlian' => 'Beginner',
                    'metode_pelatihan' => 'Hybrid',
                    'jarak_maksimal' => 50,
                ])
            ]
        );

        // 4. Trigger Recommendation Engine
        $engine = new \App\Services\RecommendationEngine();
        $engine->generateForUser($userTester->id);
    }
}
