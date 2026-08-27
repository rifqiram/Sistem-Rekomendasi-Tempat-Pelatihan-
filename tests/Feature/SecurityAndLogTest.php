<?php

namespace Tests\Feature;

use App\Models\LogActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAndLogTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user', 'is_active' => true]);
    }

    /**
     * Test Security / Account Status
     */
    public function test_admin_can_block_user()
    {
        $this->actingAs($this->admin);

        $response = $this->patchJson("/api/admin/users/{$this->user->id}/status", [
            'is_active' => false
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tabel_users', [
            'id' => $this->user->id,
            'is_active' => false
        ]);
    }

    public function test_blocked_user_cannot_access_protected_routes()
    {
        // First block the user
        $this->user->update(['is_active' => false]);

        $this->actingAs($this->user);

        // Try to access a protected route (which uses SystemAuth middleware)
        $response = $this->getJson('/api/profile');

        // Middleware should intercept and return 403 Forbidden with custom message
        $response->assertStatus(403)
                 ->assertJsonPath('message', 'Akun Anda dinonaktifkan oleh administrator.');
    }

    public function test_unauthorized_when_no_token_provided()
    {
        // Not acting as anyone, no token
        $response = $this->getJson('/api/profile');

        // Laravel default authenticate middleware returns 401
        $response->assertStatus(401);
    }

    /**
     * Test Audit Trail (Log Activity)
     */
    public function test_login_creates_activity_log()
    {
        // We need the raw password for login
        $password = 'password123';
        $loginUser = User::factory()->create([
            'role' => 'user',
            'password' => bcrypt($password)
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $loginUser->email,
            'password' => $password
        ]);

        $response->assertStatus(200);

        // Assert log was created automatically by AuthController
        $this->assertDatabaseHas('log_activities', [
            'user_id' => $loginUser->id,
            'activity_type' => 'login'
        ]);
    }

    public function test_enrollment_creates_activity_log()
    {
        $this->actingAs($this->user);

        $tc = \App\Models\TrainingCenter::create(['nama' => 'TC', 'alamat' => 'A', 'telepon' => '1']);
        $pelatihan = \App\Models\Pelatihan::create([
            'judul' => 'Pelatihan Log',
            'training_center_id' => $tc->id,
            'is_active' => true,
            'interest_category' => 'IT',
            'method' => 'Online',
            'required_skill' => 'Beginner',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-01-10'
        ]);

        $response = $this->postJson('/api/enrollments', [
            'pelatihan_id' => $pelatihan->id,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('log_activities', [
            'user_id' => $this->user->id,
            'activity_type' => 'enroll',
            'pelatihan_id' => $pelatihan->id,
        ]);
    }

    public function test_admin_can_view_and_delete_log_activity()
    {
        $this->actingAs($this->admin);

        $log = LogActivity::create([
            'user_id' => $this->user->id,
            'activity_type' => 'test_action'
        ]);

        // View
        $responseView = $this->getJson('/api/admin/log-activities');
        $responseView->assertStatus(200);
        $this->assertGreaterThan(0, count($responseView->json('data')));

        // Delete
        $responseDelete = $this->deleteJson("/api/admin/log-activities/{$log->id}");
        $responseDelete->assertStatus(200);
        $this->assertDatabaseMissing('log_activities', ['id' => $log->id]);
    }

    public function test_user_cannot_delete_log_activity()
    {
        $this->actingAs($this->user);

        $log = LogActivity::create([
            'user_id' => $this->admin->id,
            'activity_type' => 'admin_action'
        ]);

        $response = $this->deleteJson("/api/admin/log-activities/{$log->id}");
        $response->assertStatus(403);
    }
}