<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MediaModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'email' => 'admin@tiochu.com',
        ]);
    }

    /**
     * Genera un archivo PNG válido en bytes para pruebas sin depender de la extensión GD.
     */
    private function createFakePng(string $filename = 'test.png'): UploadedFile
    {
        // PNG 1x1 transparente válido
        $pngBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        $content = base64_decode($pngBase64);
        
        $tempPath = tempnam(sys_get_temp_dir(), 'test_img_');
        file_put_contents($tempPath, $content);

        return new UploadedFile(
            $tempPath,
            $filename,
            'image/png',
            null,
            true
        );
    }

    public function test_guest_is_redirected_from_media(): void
    {
        $response = $this->get(route('media.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_media_gallery(): void
    {
        $response = $this->actingAs($this->user)->get(route('media.index'));

        $response->assertStatus(200);
        $response->assertSee('Multimedia');
        $response->assertSee('Logo Oficial');
        $response->assertSee('Fondo de Login');
    }

    public function test_system_assets_are_automatically_registered(): void
    {
        $this->actingAs($this->user)->get(route('media.index'));

        $this->assertDatabaseHas('media_assets', [
            'system_key' => 'logo',
            'is_system' => true,
        ]);

        $this->assertDatabaseHas('media_assets', [
            'system_key' => 'login_background',
            'is_system' => true,
        ]);
    }

    public function test_user_can_upload_image_to_gallery(): void
    {
        $fakeImage = $this->createFakePng('singani_casa_real.png');

        $response = $this->actingAs($this->user)->post(route('media.store'), [
            'title' => 'Singani Casa Real',
            'category' => 'drinks',
            'image' => $fakeImage,
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('media_assets', [
            'title' => 'Singani Casa Real',
            'category' => 'drinks',
            'is_system' => false,
        ]);

        $asset = MediaAsset::where('title', 'Singani Casa Real')->first();
        $this->assertNotNull($asset);
        $this->assertTrue(File::exists(public_path($asset->path)));

        // Limpiar archivo de prueba
        if (File::exists(public_path($asset->path))) {
            File::delete(public_path($asset->path));
        }
    }

    public function test_user_cannot_delete_system_asset(): void
    {
        $this->actingAs($this->user)->get(route('media.index'));
        $systemLogo = MediaAsset::where('system_key', 'logo')->firstOrFail();

        $response = $this->actingAs($this->user)->delete(route('media.destroy', $systemLogo));

        $this->assertDatabaseHas('media_assets', [
            'id' => $systemLogo->id,
            'system_key' => 'logo',
        ]);
    }

    public function test_user_can_delete_regular_gallery_asset(): void
    {
        $fakeImage = $this->createFakePng('staff_bartender.png');

        $this->actingAs($this->user)->post(route('media.store'), [
            'title' => 'Bartender Turno Noche',
            'category' => 'staff',
            'image' => $fakeImage,
        ]);

        $asset = MediaAsset::where('title', 'Bartender Turno Noche')->firstOrFail();

        $response = $this->actingAs($this->user)->delete(route('media.destroy', $asset));
        $response->assertRedirect(route('media.index'));

        $this->assertDatabaseMissing('media_assets', [
            'id' => $asset->id,
        ]);
    }

    public function test_user_can_update_profile_avatar_directly(): void
    {
        $fakeAvatar = $this->createFakePng('don_ludo_avatar.png');

        $response = $this->actingAs($this->user)->post(route('profile.avatar.update'), [
            'avatar' => $fakeAvatar,
        ]);

        $response->assertRedirect();

        $this->user->refresh();
        $this->assertNotNull($this->user->avatar);
        $this->assertNotNull($this->user->avatar_url);
        $this->assertTrue(File::exists(public_path($this->user->avatar)));

        // Limpiar archivo
        if (File::exists(public_path($this->user->avatar))) {
            File::delete(public_path($this->user->avatar));
        }
    }

    public function test_user_can_set_gallery_asset_as_profile_avatar(): void
    {
        $fakeImage = $this->createFakePng('ludo_photo.png');

        $this->actingAs($this->user)->post(route('media.store'), [
            'title' => 'Foto Don Ludo',
            'category' => 'staff',
            'image' => $fakeImage,
        ]);

        $asset = MediaAsset::where('title', 'Foto Don Ludo')->firstOrFail();

        $response = $this->actingAs($this->user)->post(route('media.setSystem', $asset), [
            'target' => 'avatar',
        ]);

        $response->assertRedirect();

        $this->user->refresh();
        $this->assertNotNull($this->user->avatar);
        $this->assertTrue(File::exists(public_path($this->user->avatar)));

        // Limpiar archivo
        if (File::exists(public_path($this->user->avatar))) {
            File::delete(public_path($this->user->avatar));
        }
        if (File::exists(public_path($asset->path))) {
            File::delete(public_path($asset->path));
        }
    }

    public function test_user_can_remove_avatar(): void
    {
        $fakeAvatar = $this->createFakePng('temp_avatar.png');

        $this->actingAs($this->user)->post(route('profile.avatar.update'), [
            'avatar' => $fakeAvatar,
        ]);

        $this->user->refresh();
        $this->assertNotNull($this->user->avatar);

        $response = $this->actingAs($this->user)->delete(route('profile.avatar.remove'));
        $response->assertRedirect();

        $this->user->refresh();
        $this->assertNull($this->user->avatar);
        $this->assertNull($this->user->avatar_url);
    }
}

