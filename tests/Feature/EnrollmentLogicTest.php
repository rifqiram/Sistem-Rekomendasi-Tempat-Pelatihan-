<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Pelatihan;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentLogicTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;
    protected Pelatihan $pelatihan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);

        $tc = TrainingCenter::create([
            'nama' => 'Pusat Test',
            'alamat' => 'Alamat',
            'telepon' => '000'
        ]);

        $this->pelatihan = Pelatihan::create([
            'judul' => 'Kursus A',
            'training_center_id' => $tc->id,
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-01-10',
            'is_active' => true
        ]);
    }

    public function test_user_can_enroll_training()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/enrollments', [
            'pelatihan_id' => $this->pelatihan->id
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->user->id,
            'pelatihan_id' => $this->pelatihan->id,
            'status' => 'pending'
        ]);
    }

    public function test_user_cannot_enroll_same_training_twice()
    {
        $this->actingAs($this->user);

        // First enrollment
        $this->postJson('/api/enrollments', [
            'pelatihan_id' => $this->pelatihan->id
        ]);

        // Second enrollment (Duplicate)
        $response = $this->postJson('/api/enrollments', [
            'pelatihan_id' => $this->pelatihan->id
        ]);

        $response->assertStatus(409)
                 ->assertJsonPath('message', 'Anda sudah terdaftar pada pelatihan ini.');
    }

    public function test_admin_can_reject_enrollment()
    {
        $enrollment = Enrollment::create([
            'user_id' => $this->user->id,
            'training_center_id' => $this->pelatihan->training_center_id,
            'pelatihan_id' => $this->pelatihan->id,
            'tanggal_daftar' => now(),
            'status' => 'pending'
        ]);

        $this->actingAs($this->admin);

        $response = $this->patchJson("/api/admin/enrollments/{$enrollment->id}/status", [
            'status' => 'rejected'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'status' => 'rejected'
        ]);
    }

    public function test_admin_cannot_set_invalid_enrollment_status()
    {
        $enrollment = Enrollment::create([
            'user_id' => $this->user->id,
            'training_center_id' => $this->pelatihan->training_center_id,
            'pelatihan_id' => $this->pelatihan->id,
            'tanggal_daftar' => now(),
            'status' => 'pending'
        ]);

        $this->actingAs($this->admin);

        $response = $this->patchJson("/api/admin/enrollments/{$enrollment->id}/status", [
            'status' => 'terdaftar' // invalid old status
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_see_own_enrollment_history()
    {
        Enrollment::create([
            'user_id' => $this->user->id,
            'training_center_id' => $this->pelatihan->training_center_id,
            'pelatihan_id' => $this->pelatihan->id,
            'tanggal_daftar' => now(),
            'status' => 'pending'
        ]);

        $this->actingAs($this->user);
        $response = $this->getJson('/api/enrollments');

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }

    public function test_user_cannot_see_other_users_enrollments()
    {
        $otherUser = User::factory()->create(['role' => 'user']);
        
        Enrollment::create([
            'user_id' => $otherUser->id,
            'training_center_id' => $this->pelatihan->training_center_id,
            'pelatihan_id' => $this->pelatihan->id,
            'tanggal_daftar' => now(),
            'status' => 'pending'
        ]);

        $this->actingAs($this->user);
        $response = $this->getJson('/api/enrollments');

        $response->assertStatus(200)->assertJsonCount(0, 'data');
    }

    public function test_admin_can_update_enrollment_status()
    {
        // Setup initial enrollment
        $enrollment = Enrollment::create([
            'user_id' => $this->user->id,
            'training_center_id' => $this->pelatihan->training_center_id,
            'pelatihan_id' => $this->pelatihan->id,
            'tanggal_daftar' => now(),
            'status' => 'pending'
        ]);

        $this->actingAs($this->admin);

        // Update status to approved
        $response = $this->patchJson("/api/admin/enrollments/{$enrollment->id}/status", [
            'status' => 'approved'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'status' => 'approved'
        ]);
    }

    public function test_user_cannot_update_enrollment_status()
    {
        $enrollment = Enrollment::create([
            'user_id' => $this->user->id,
            'training_center_id' => $this->pelatihan->training_center_id,
            'pelatihan_id' => $this->pelatihan->id,
            'tanggal_daftar' => now(),
            'status' => 'pending'
        ]);

        $this->actingAs($this->user);

        // Update status to approved
        $response = $this->patchJson("/api/admin/enrollments/{$enrollment->id}/status", [
            'status' => 'approved'
        ]);

        $response->assertStatus(403);
    }
}