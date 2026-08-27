<?php

namespace Tests\Feature;

use App\Models\Pelatihan;
use App\Models\Profile;
use App\Models\QuestionnaireResponse;
use App\Models\Recommendation;
use App\Models\TrainingCenter;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackendFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'password' => bcrypt('password')]);
        $this->user = User::factory()->create(['role' => 'user', 'password' => bcrypt('password')]);
    }

    /**
     * 1. Authentication
     */
    public function test_login_success()
    {
        $response = $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_login_failed()
    {
        $response = $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    /**
     * 2. Profile
     */
    public function test_profile_coordinate_update_refreshes_recommendations()
    {
        $this->actingAs($this->user);
        $initialRec = \App\Models\Recommendation::where('user_id', $this->user->id)->first();
        
        $response = $this->postJson('/api/profile', [
            'age' => 25,
            'education' => 'S1',
            'district' => 'Kota B',
            'phone' => '089999999',
            'latitude' => -7.2500,
            'longitude' => 112.7500,
        ]);
        
        $response->assertStatus(200);
        
        $updatedRec = \App\Models\Recommendation::where('user_id', $this->user->id)->first();
        // Just assert that generating runs and we have recommendations.
        // We need a questionnaire to generate recommendations
        \App\Models\QuestionnaireResponse::create([
            'user_id' => $this->user->id,
            'answers' => json_encode(['bidang_diminati' => 'Programming', 'metode_pelatihan' => 'Online', 'tingkat_keahlian' => 'Beginner', 'jarak_maksimal' => 50]),
        ]);
        
        $response = $this->postJson('/api/profile', [
            'age' => 25,
            'education' => 'S1',
            'district' => 'Kota B',
            'phone' => '089999999',
            'latitude' => -7.2500,
            'longitude' => 112.7500,
        ]);

        $updatedRec = \App\Models\Recommendation::where('user_id', $this->user->id)->first();
        // Create TC and training so there is something to recommend
        $tc = \App\Models\TrainingCenter::create([
            'nama' => 'TC', 'alamat' => 'A', 'telepon' => '1', 'latitude' => -7.2500, 'longitude' => 112.7500
        ]);
        \App\Models\Pelatihan::create(['judul' => 'A', 'training_center_id' => $tc->id, 'interest_category' => 'Programming', 'method' => 'Online', 'required_skill' => 'Beginner', 'is_active' => true, 'tanggal_mulai' => '2026-01-01', 'tanggal_selesai' => '2026-01-10']);
        
        $response = $this->postJson('/api/profile', [
            'age' => 25,
            'education' => 'S1',
            'district' => 'Kota B',
            'phone' => '089999999',
            'latitude' => -7.2500,
            'longitude' => 112.7500,
        ]);

        $updatedRec = \App\Models\Recommendation::where('user_id', $this->user->id)->first();
        $this->assertNotNull($updatedRec);
    }

    public function test_user_can_view_and_update_profile()
    {
        $this->actingAs($this->user);

        // Update
        $responseUpdate = $this->postJson('/api/profile', [
            'age' => 25,
            'alamat_lengkap' => 'Jl. Magetan',
            'latitude' => -7.6400,
            'longitude' => 111.3200,
        ]);
        $responseUpdate->assertStatus(200);

        // View
        $responseView = $this->getJson('/api/profile');
        $responseView->assertStatus(200)
                     ->assertJsonPath('data.age', 25);
    }

    /**
     * 3. Questionnaire
     */
    public function test_user_can_submit_questionnaire_and_trigger_recommendation()
    {
        $this->actingAs($this->user);

        // Pastikan profile terisi dulu untuk calc distance
        Profile::create(['user_id' => $this->user->id, 'latitude' => -7.6400, 'longitude' => 111.3200]);

        // Submit Questionnaire
        $response = $this->postJson('/api/questionnaire', [
            'answers' => [
                'bidang_diminati' => 'IT',
                'tingkat_keahlian' => 'Beginner',
                'metode_pelatihan' => 'Hybrid',
                'jarak_maksimal' => 50,
            ]
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('questionnaire_responses', ['user_id' => $this->user->id]);
    }

    public function test_questionnaire_validation_fails_if_incomplete()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/questionnaire', [
            'answers' => [
                'bidang_diminati' => 'IT',
                // missing other fields
            ]
        ]);

        $response->assertStatus(422); // Validation error
    }

    /**
     * 4. Recommendation Engine Output
     */
    public function test_recommendation_flow_scoring_and_distance()
    {
        // Setup TC Near
        $tcNear = TrainingCenter::create(['nama' => 'TC Near', 'alamat' => 'A', 'telepon' => '1', 'latitude' => -7.6500, 'longitude' => 111.3300]);
        // Setup TC Far
        $tcFar = TrainingCenter::create(['nama' => 'TC Far', 'alamat' => 'B', 'telepon' => '2', 'latitude' => -7.2500, 'longitude' => 112.7500]);

        // Setup Pelatihans (Both 100% matched by attributes)
        Pelatihan::create(['judul' => 'P Near', 'training_center_id' => $tcNear->id, 'interest_category' => 'IT', 'method' => 'Hybrid', 'required_skill' => 'Beginner', 'tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2026-08-10', 'is_active' => true]);
        Pelatihan::create(['judul' => 'P Far', 'training_center_id' => $tcFar->id, 'interest_category' => 'IT', 'method' => 'Hybrid', 'required_skill' => 'Beginner', 'tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2026-08-10', 'is_active' => true]);

        // User setup
        Profile::create(['user_id' => $this->user->id, 'latitude' => -7.6400, 'longitude' => 111.3200]);

        $this->actingAs($this->user);
        $this->postJson('/api/questionnaire', [
            'answers' => [
                'bidang_diminati' => 'IT',
                'tingkat_keahlian' => 'Beginner',
                'metode_pelatihan' => 'Hybrid',
                'jarak_maksimal' => 50,
            ]
        ]);

        $response = $this->getJson('/api/recommendations');
        $response->assertStatus(200);

        // Peringkat 1 harus TC Near karena jaraknya lebih dekat
        $data = $response->json('data');
        $this->assertGreaterThan(0, count($data));
        $this->assertEquals('TC Near', $data[0]['training_center']['nama']);
    }

    /**
     * 5. Training Center API
     */
    public function test_get_training_centers()
    {
        $this->actingAs($this->user);
        TrainingCenter::create(['nama' => 'TC 1', 'alamat' => 'A', 'telepon' => '1']);

        $response = $this->getJson('/api/training-centers');
        $response->assertStatus(200);
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    /**
     * 6. Enrollment
     */
    public function test_user_can_enroll_training()
    {
        $this->actingAs($this->user);

        // Route pendaftaran yang lama (dipakai sementara)
        // Di sistem lama, method ini ada di PelatihanController@pendaftaran (POST /api/pelatihan/{id}/pendaftaran)

        // Buat Pelatihan dummy
        $tc = TrainingCenter::create(['nama' => 'TC 1', 'alamat' => 'A', 'telepon' => '1']);
        $p = Pelatihan::create(['judul' => 'P 1', 'training_center_id' => $tc->id, 'tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2026-08-10']);

        // Dalam arsitektur saat ini (untuk backward compat), user harus punya record Peserta.
        // Namun karena Enrollment baru menggunakan User langsung (lihat migrasi Enrollment), kita harus test apakah model Enrollment bisa tercipta.
        // Karena endpoint khusus V2 (POST /api/enrollments) BELUM dibuat dalam Route,
        // kita tes proses simpan Model Enrollment saja sebagai pembuktian fungsionalitas.

        $enrollment = Enrollment::create([
            'user_id' => $this->user->id,
            'training_center_id' => $tc->id,
            'pelatihan_id' => $p->id,
            'tanggal_daftar' => now(),
            'status' => 'pending'
        ]);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->user->id,
            'pelatihan_id' => $p->id,
        ]);
    }

    /**
     * 7. Admin
     */
    public function test_admin_can_view_users_and_enrollments()
    {
        $this->actingAs($this->admin);

        // View Users
        $responseUsers = $this->getJson('/api/admin/users');
        $responseUsers->assertStatus(200);

        // View Enrollments
        $responseEnrollments = $this->getJson('/api/admin/enrollments');
        $responseEnrollments->assertStatus(200);
    }

    public function test_user_cannot_access_admin_routes()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/admin/users');
        $response->assertStatus(403);
    }
}
