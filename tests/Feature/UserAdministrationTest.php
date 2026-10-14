<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAdministrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'DonLudo@gmail.com')->first();
    }

    public function test_guest_is_redirected_from_users_index(): void
    {
        $response = $this->get(route('users.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_users_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.index'));
        $response->assertStatus(200);
        $response->assertSee('Administración de Usuarios');
        $response->assertSee('Don Ludo');
        $response->assertSee('DonLudo@gmail.com');
    }

    public function test_admin_can_create_new_user_and_new_user_can_login(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name'     => 'Ariel Subterráneo',
            'email'    => 'ariel@tiochu.com',
            'password' => 'barrabar123',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'name'  => 'Ariel Subterráneo',
            'email' => 'ariel@tiochu.com',
        ]);

        $newUser = User::where('email', 'ariel@tiochu.com')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue(Hash::check('barrabar123', $newUser->password));

        // Probar que este nuevo usuario puede iniciar sesión en el sistema
        $loginResponse = $this->post(route('login'), [
            'email'    => 'ariel@tiochu.com',
            'password' => 'barrabar123',
        ]);

        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($newUser);
    }

    public function test_admin_can_update_user_details_and_password(): void
    {
        $targetUser = User::create([
            'name'     => 'Kelly Principal',
            'email'    => 'kelly@tiochu.com',
            'password' => Hash::make('oldpassword'),
        ]);

        $response = $this->actingAs($this->admin)->put(route('users.update', $targetUser), [
            'name'     => 'Kelly Administradora Barra',
            'email'    => 'kelly_barra@tiochu.com',
            'password' => 'newpassword123',
        ]);

        $response->assertSessionHas('success');
        $targetUser->refresh();

        $this->assertEquals('Kelly Administradora Barra', $targetUser->name);
        $this->assertEquals('kelly_barra@tiochu.com', $targetUser->email);
        $this->assertTrue(Hash::check('newpassword123', $targetUser->password));
    }

    public function test_cannot_delete_active_self_or_master_don_ludo(): void
    {
        // Intento de eliminarse a sí mismo
        $responseSelf = $this->actingAs($this->admin)->delete(route('users.destroy', $this->admin));
        $responseSelf->assertSessionHasErrors('delete_error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);

        // Intento de eliminar a Don Ludo desde otro usuario
        $otherAdmin = User::create([
            'name'     => 'Admin 2',
            'email'    => 'admin2@tiochu.com',
            'password' => Hash::make('pass12345'),
        ]);

        $responseDonLudo = $this->actingAs($otherAdmin)->delete(route('users.destroy', $this->admin));
        $responseDonLudo->assertSessionHasErrors('delete_error');
        $this->assertDatabaseHas('users', ['email' => 'DonLudo@gmail.com']);
    }

    public function test_can_delete_auxiliary_user(): void
    {
        $aux = User::create([
            'name'     => 'Temporal',
            'email'    => 'temp@tiochu.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $aux));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $aux->id]);
    }
}
