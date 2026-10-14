<?php

namespace App\Http\Controllers;

use App\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Muestra la galería multimedia con activos del sistema y biblioteca.
     */
    public function index(Request $request)
    {
        $this->ensureSystemAssetsExist();

        $category = $request->query('category', 'all');

        $query = MediaAsset::query();

        if ($category !== 'all') {
            $query->where('category', $category);
        }

        $mediaAssets = $query->orderBy('is_system', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->get();

        $systemLogo = MediaAsset::where('system_key', 'logo')->first();
        $systemBackground = MediaAsset::where('system_key', 'login_background')->first();

        // Conteos por categoría para los filtros
        $counts = [
            'all' => MediaAsset::count(),
            'system' => MediaAsset::where('category', 'system')->count(),
            'drinks' => MediaAsset::where('category', 'drinks')->count(),
            'establishment' => MediaAsset::where('category', 'establishment')->count(),
            'staff' => MediaAsset::where('category', 'staff')->count(),
            'general' => MediaAsset::where('category', 'general')->count(),
        ];

        return view('media.index', compact(
            'mediaAssets',
            'systemLogo',
            'systemBackground',
            'category',
            'counts'
        ));
    }

    /**
     * Sube una nueva imagen a la galería multimedia.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:120',
            'category' => 'required|in:drinks,establishment,staff,general',
            'image' => 'required|image|mimes:png,jpg,jpeg,webp,svg|max:10240', // Max 10MB
        ]);

        $file = $request->file('image');
        $galleryDir = public_path('images/gallery');

        if (!File::isDirectory($galleryDir)) {
            File::makeDirectory($galleryDir, 0755, true);
        }

        $extension = $file->getClientOriginalExtension();
        $slugTitle = Str::slug($request->input('title'));
        $filename = ($slugTitle ?: 'media') . '_' . time() . '.' . $extension;
        $file->move($galleryDir, $filename);

        $relPath = 'images/gallery/' . $filename;
        $fullPath = public_path($relPath);

        $dimensions = null;
        if (file_exists($fullPath)) {
            $imageInfo = @getimagesize($fullPath);
            if ($imageInfo) {
                $dimensions = $imageInfo[0] . 'x' . $imageInfo[1];
            }
        }

        MediaAsset::create([
            'title' => $request->input('title'),
            'filename' => $filename,
            'path' => $relPath,
            'category' => $request->input('category'),
            'is_system' => false,
            'file_size' => file_exists($fullPath) ? filesize($fullPath) : $file->getSize(),
            'dimensions' => $dimensions,
            'mime_type' => $file->getClientMimeType(),
        ]);

        return redirect()->route('media.index', ['category' => $request->input('category')])
            ->with('status', 'Imagen agregada a la galería correctamente.');
    }

    /**
     * Reemplaza el archivo físico de un activo existente (incluyendo activos oficiales).
     */
    public function replace(Request $request, MediaAsset $media)
    {
        $request->validate([
            'replacement_image' => 'required|image|mimes:png,jpg,jpeg,webp,svg|max:10240',
        ]);

        $file = $request->file('replacement_image');

        if ($media->is_system) {
            // Activo oficial del sistema
            $destinationPath = public_path($media->path);
            
            // Si es el logo oficial y se sube otro formato, lo convertimos o guardamos directamente
            $file->move(dirname($destinationPath), basename($destinationPath));

            $dimensions = null;
            if (file_exists($destinationPath)) {
                $imageInfo = @getimagesize($destinationPath);
                if ($imageInfo) {
                    $dimensions = $imageInfo[0] . 'x' . $imageInfo[1];
                }
            }

            $media->update([
                'file_size' => file_exists($destinationPath) ? filesize($destinationPath) : null,
                'dimensions' => $dimensions,
                'updated_at' => now(),
            ]);

            return redirect()->route('media.index')
                ->with('status', 'Activo del sistema actualizado correctamente.');
        }

        // Activo común de la galería
        $galleryDir = public_path('images/gallery');
        if (!File::isDirectory($galleryDir)) {
            File::makeDirectory($galleryDir, 0755, true);
        }

        // Eliminar archivo anterior si existe
        $oldFullPath = public_path($media->path);
        if (File::exists($oldFullPath)) {
            File::delete($oldFullPath);
        }

        $extension = $file->getClientOriginalExtension();
        $slugTitle = Str::slug($media->title);
        $filename = ($slugTitle ?: 'media') . '_' . time() . '.' . $extension;
        $file->move($galleryDir, $filename);

        $relPath = 'images/gallery/' . $filename;
        $fullPath = public_path($relPath);

        $dimensions = null;
        if (file_exists($fullPath)) {
            $imageInfo = @getimagesize($fullPath);
            if ($imageInfo) {
                $dimensions = $imageInfo[0] . 'x' . $imageInfo[1];
            }
        }

        $media->update([
            'filename' => $filename,
            'path' => $relPath,
            'file_size' => file_exists($fullPath) ? filesize($fullPath) : null,
            'dimensions' => $dimensions,
            'mime_type' => $file->getClientMimeType(),
            'updated_at' => now(),
        ]);

        return redirect()->route('media.index')
            ->with('status', 'Imagen reemplazada exitosamente.');
    }

    /**
     * Establece una imagen de la galería como recurso del sistema o foto de perfil.
     */
    public function setAsSystem(Request $request, MediaAsset $media)
    {
        $request->validate([
            'target' => 'required|in:logo,login_background,avatar',
        ]);

        $target = $request->input('target');
        $sourcePath = public_path($media->path);

        if (!File::exists($sourcePath)) {
            return back()->withErrors(['target' => 'El archivo de origen no existe en el servidor.']);
        }

        // Si es foto de perfil del usuario en sesión
        if ($target === 'avatar') {
            $user = $request->user();
            $avatarDir = public_path('images/avatars');
            if (!File::isDirectory($avatarDir)) {
                File::makeDirectory($avatarDir, 0755, true);
            }

            $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
            $filename = 'user_' . $user->id . '_' . time() . '.' . $extension;
            $destination = $avatarDir . DIRECTORY_SEPARATOR . $filename;

            File::copy($sourcePath, $destination);

            $user->update([
                'avatar' => 'images/avatars/' . $filename,
            ]);

            return back()->with('status', 'Foto de perfil actualizada correctamente.');
        }

        $targetPathRel = $target === 'logo' ? 'images/LogoTioChu.png' : 'images/fondoTioChuLogo.png';
        $targetPathFull = public_path($targetPathRel);

        // Copiar sobre el activo oficial
        File::copy($sourcePath, $targetPathFull);

        // Actualizar registro en base de datos
        $systemAsset = MediaAsset::where('system_key', $target)->first();
        if ($systemAsset) {
            $imageInfo = @getimagesize($targetPathFull);
            $dimensions = $imageInfo ? ($imageInfo[0] . 'x' . $imageInfo[1]) : null;

            $systemAsset->update([
                'file_size' => filesize($targetPathFull),
                'dimensions' => $dimensions,
                'updated_at' => now(),
            ]);
        }

        $label = $target === 'logo' ? 'Logo Oficial de Tío Chu' : 'Fondo del Inicio de Sesión';
        return redirect()->route('media.index')
            ->with('status', "{$label} actualizado correctamente.");
    }

    /**
     * Actualiza la foto de perfil del usuario autenticado.
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:png,jpg,jpeg,webp,svg|max:10240',
        ]);

        $file = $request->file('avatar');
        $avatarDir = public_path('images/avatars');

        if (!File::isDirectory($avatarDir)) {
            File::makeDirectory($avatarDir, 0755, true);
        }

        $user = $request->user();

        // Borrar avatar previo si existía en avatars/
        if ($user->avatar && File::exists(public_path($user->avatar))) {
            File::delete(public_path($user->avatar));
        }

        $extension = $file->getClientOriginalExtension();
        $filename = 'user_' . $user->id . '_' . time() . '.' . $extension;
        $file->move($avatarDir, $filename);

        $relPath = 'images/avatars/' . $filename;

        $user->update([
            'avatar' => $relPath,
        ]);

        return back()->with('status', 'Foto de perfil actualizada correctamente.');
    }

    /**
     * Elimina la foto de perfil del usuario y regresa al avatar por defecto con inicial.
     */
    public function removeAvatar(Request $request)
    {
        $user = $request->user();

        if ($user->avatar && File::exists(public_path($user->avatar))) {
            File::delete(public_path($user->avatar));
        }

        $user->update([
            'avatar' => null,
        ]);

        return back()->with('status', 'Foto de perfil removida.');
    }

    /**
     * Elimina un recurso multimedia de la galería.
     */
    public function destroy(MediaAsset $media)
    {
        if ($media->is_system) {
            return back()->withErrors(['error' => 'No puedes eliminar un recurso base del sistema.']);
        }

        $fullPath = public_path($media->path);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }

        $media->delete();

        return redirect()->route('media.index')
            ->with('status', 'Imagen eliminada de la galería.');
    }

    /**
     * Inicializa los registros para Logo Oficial y Fondo de Login si no existen en BD.
     */
    private function ensureSystemAssetsExist(): void
    {
        // 1. Logo Oficial
        $logoPath = 'images/LogoTioChu.png';
        $logoFull = public_path($logoPath);
        if (!MediaAsset::where('system_key', 'logo')->exists() && File::exists($logoFull)) {
            $info = @getimagesize($logoFull);
            MediaAsset::create([
                'title' => 'Logo Oficial Tío Chu',
                'filename' => 'LogoTioChu.png',
                'path' => $logoPath,
                'category' => 'system',
                'is_system' => true,
                'system_key' => 'logo',
                'file_size' => filesize($logoFull),
                'dimensions' => $info ? ($info[0] . 'x' . $info[1]) : null,
                'mime_type' => 'image/png',
            ]);
        }

        // 2. Fondo de Login
        $bgPath = 'images/fondoTioChuLogo.png';
        $bgFull = public_path($bgPath);
        if (!MediaAsset::where('system_key', 'login_background')->exists() && File::exists($bgFull)) {
            $info = @getimagesize($bgFull);
            MediaAsset::create([
                'title' => 'Fondo Inicio de Sesión',
                'filename' => 'fondoTioChuLogo.png',
                'path' => $bgPath,
                'category' => 'system',
                'is_system' => true,
                'system_key' => 'login_background',
                'file_size' => filesize($bgFull),
                'dimensions' => $info ? ($info[0] . 'x' . $info[1]) : null,
                'mime_type' => 'image/png',
            ]);
        }
    }
}
