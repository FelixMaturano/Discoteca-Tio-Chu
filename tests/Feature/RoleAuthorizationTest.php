<?php

namespace Tests\Feature;

use App\Models\NightSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_user_administration_or_reopen_sessions(): void
    {
        $user = User::factory()->create(['role' => 'cajero']);
        $session = NightSession::create([
            'session_date' => '2026-11-18',
            'day_name' => 'Miércoles',
            'pos_commission_rate' => 0.05,
            'opening_cash' => 100,
            'status' => 'closed',
        ]);

        $this->actingAs($user)
            ->get(route('users.index'))
            ->assertForbidden();

        $this->post(route('closing.reopen', $session))
            ->assertForbidden();
    }

    public function test_non_admin_cannot_delete_or_change_session_status(): void
    {
        $user = User::factory()->create(['role' => 'bodeguero']);
        $session = NightSession::create([
            'session_date' => '2026-11-19',
            'day_name' => 'Jueves',
            'pos_commission_rate' => 0.05,
            'opening_cash' => 100,
            'status' => 'open',
        ]);

        $this->actingAs($user)
            ->delete(route('sessions.destroy', $session))
            ->assertForbidden();

        $this->patch(route('sessions.updateStatus', $session), [
            'status' => 'closed',
        ])->assertForbidden();

        $this->assertDatabaseHas('night_sessions', [
            'id' => $session->id,
            'status' => 'open',
        ]);
    }

    public function test_server_routes_require_an_admin_in_the_local_environment(): void
    {
        $this->app->instance('env', 'local');
        $user = User::factory()->create(['role' => 'bartender']);

        $this->actingAs($user)
            ->get(route('server.status'))
            ->assertForbidden();

        $token = csrf_token();
        $this->withSession(['_token' => $token])
            ->post(route('server.toggle'), ['_token' => $token])
            ->assertForbidden();
    }

    public function test_server_routes_are_forbidden_outside_the_local_environment(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->get(route('server.status'))
            ->assertForbidden();
    }
}
