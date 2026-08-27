<?php

namespace Tests\Feature;

use App\Models\Pelatihan;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Users
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
    }

    /**
     * Test Training Center CRUD
     */
    public function test_admin_can_create_training_center(): void
    {
        $this->actingAs($this->admin);

        $payload = [
            'nama' => 'Pusat Pelatihan Unggul',
            'alamat' => 'Jl. Merdeka No 1',
            'telepon' => '08123456789',
            'latitude' => -7.2500,
            'longitude' => 112.7500,
            'status' => 'active'
        ];

        $response = $this->postJson('/api/training-centers', $payload);

        $response->assertStatus(201)
                 ->assertJsonPath('data.nama', 'Pusat Pelatihan Unggul');

        $this->assertDatabaseHas('training_centers', ['nama' => 'Pusat Pelatihan Unggul']);
    }

    public function test_user_cannot_create_training_center(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/training-centers', [
            'nama' => 'Pusat Pelatihan A'
        ]);

        $response->assertStatus(403);
    }

    public function test_training_center_accepts_valid_google_maps_url(): void
    {
        $this->actingAs($this->admin);
        $response = $this->postJson('/api/training-centers', [
            'nama' => 'TC Maps', 
            'alamat' => 'A', 
            'telepon' => '1',
            'latitude' => '-6.200',
            'longitude' => '106.816',
            'google_maps_url' => 'https://maps.google.com/?q=TC+Test',
        ]);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('training_centers', ['google_maps_url' => 'https://maps.google.com/?q=TC+Test']);
    }

    public function test_training_center_rejects_invalid_google_maps_url(): void
    {
        $this->actingAs($this->admin);
        $response = $this->postJson('/api/training-centers', [
            'nama' => 'TC Bad', 
            'alamat' => 'A', 
            'telepon' => '1',
            'google_maps_url' => 'bukan-url-valid',
        ]);
        
        $response->assertStatus(422);
    }

    public function test_admin_can_update_training_center(): void
    {
        $this->actingAs($this->admin);

        $tc = TrainingCenter::create([
            'nama' => 'TC Lama',
            'alamat' => 'Alamat',
            'telepon' => '000',
        ]);

        $response = $this->putJson("/api/training-centers/{$tc->id}", [
            'nama' => 'TC Baru Updated',
            'alamat' => 'Alamat',
            'telepon' => '000',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('training_centers', ['id' => $tc->id, 'nama' => 'TC Baru Updated']);
    }

    public function test_admin_can_delete_training_center(): void
    {
        $this->actingAs($this->admin);

        $tc = TrainingCenter::create([
            'nama' => 'TC Will Delete',
            'alamat' => 'Alamat',
            'telepon' => '000',
        ]);

        $response = $this->deleteJson("/api/training-centers/{$tc->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('training_centers', ['id' => $tc->id]);
    }

    /**
     * Test Cascade Deletion (Important for Data Integrity)
     */
    public function test_cascade_delete_removes_related_pelatihan(): void
    {
        $this->actingAs($this->admin);

        $tc = TrainingCenter::create([
            'nama' => 'Master TC',
            'alamat' => 'Alamat',
            'telepon' => '000',
        ]);

        $pelatihan1 = Pelatihan::create([
            'judul' => 'Modul 1',
            'training_center_id' => $tc->id,
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-01-10',
        ]);

        $pelatihan2 = Pelatihan::create([
            'judul' => 'Modul 2',
            'training_center_id' => $tc->id,
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-01-10',
        ]);

        $this->assertDatabaseHas('tabel_pelatihan', ['id' => $pelatihan1->id]);
        $this->assertDatabaseHas('tabel_pelatihan', ['id' => $pelatihan2->id]);

        // Trigger Delete on Parent
        $this->deleteJson("/api/training-centers/{$tc->id}");

        // Assert parent deleted
        $this->assertDatabaseMissing('training_centers', ['id' => $tc->id]);

        // Assert children deleted due to cascade or logic
        $this->assertDatabaseMissing('tabel_pelatihan', ['id' => $pelatihan1->id]);
        $this->assertDatabaseMissing('tabel_pelatihan', ['id' => $pelatihan2->id]);
    }

    /**
     * Test Pelatihan CRUD
     */
    public function test_admin_can_create_pelatihan(): void
    {
        $this->actingAs($this->admin);

        $tc = TrainingCenter::create([
            'nama' => 'TC A',
            'alamat' => 'Alamat',
            'telepon' => '000',
        ]);

        $payload = [
            'judul' => 'Bootcamp Golang',
            'training_center_id' => $tc->id,
            'interest_category' => 'IT',
            'method' => 'Online',
            'required_skill' => 'Intermediate',
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-11-01',
        ];

        // Updated API route to the correct unified route
        $response = $this->postJson('/api/trainings', $payload);

        $response->assertStatus(201)
                 ->assertJsonPath('data.judul', 'Bootcamp Golang');

        $this->assertDatabaseHas('tabel_pelatihan', ['judul' => 'Bootcamp Golang']);
    }

    public function test_pelatihan_validation_fails_missing_fields(): void
    {
        $this->actingAs($this->admin);

        // Required to bypass the TrainingCenter count check in PelatihanController
        TrainingCenter::create([
            'nama' => 'TC A',
            'alamat' => 'Alamat',
            'telepon' => '000',
        ]);

        // Missing required training_center_id
        $payload = [
            'judul' => 'Bootcamp Golang',
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-11-01',
        ];

        // Updated API route to the correct unified route
        $response = $this->postJson('/api/trainings', $payload);

        $response->assertStatus(422);
    }

    public function test_admin_can_delete_pelatihan(): void
    {
        $this->actingAs($this->admin);

        $tc = TrainingCenter::create([
            'nama' => 'TC A',
            'alamat' => 'Alamat',
            'telepon' => '000',
        ]);

        $pelatihan = Pelatihan::create([
            'judul' => 'Bootcamp QA',
            'training_center_id' => $tc->id,
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-11-01',
        ]);

        $response = $this->deleteJson("/api/trainings/{$pelatihan->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('tabel_pelatihan', ['id' => $pelatihan->id]);
    }

    public function test_admin_cannot_delete_pelatihan_with_enrollments(): void
    {
        $this->actingAs($this->admin);

        $tc = TrainingCenter::create([
            'nama' => 'TC B',
            'alamat' => 'Alamat B',
            'telepon' => '111',
        ]);

        $pelatihan = Pelatihan::create([
            'judul' => 'Bootcamp Backend',
            'training_center_id' => $tc->id,
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-11-01',
        ]);

        \App\Models\Enrollment::create([
            'user_id' => $this->user->id,
            'training_center_id' => $tc->id,
            'pelatihan_id' => $pelatihan->id,
            'tanggal_daftar' => now(),
            'status' => 'terdaftar',
        ]);

        $response = $this->deleteJson("/api/trainings/{$pelatihan->id}");

        $response->assertStatus(400)
                 ->assertJsonPath('message', 'Masih ada peserta terdaftar');

        $this->assertDatabaseHas('tabel_pelatihan', ['id' => $pelatihan->id]);
    }
}