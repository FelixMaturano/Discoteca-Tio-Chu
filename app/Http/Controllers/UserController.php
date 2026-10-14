<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Muestra la lista de cuentas de usuario autorizadas para ingresar al sistema.
     */
    public function index()
    {
        $users = User::orderBy('id', 'asc')->get();
        return view('users.index', compact('users'));
    }

    /**
     * Registra una nueva cuenta de usuario con credenciales de acceso.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'name.required'     => 'El nombre del usuario es obligatorio.',
            'email.required'    => 'El correo electrónico es obligatorio.',
            'email.email'       => 'Ingrese un formato de correo electrónico válido.',
            'email.unique'      => 'Este correo electrónico ya está registrado en otra cuenta.',
            'password.required' => 'La contraseña de acceso es obligatoria.',
            'password.min'      => 'La contraseña debe tener al menos 6 caracteres.',
        ]);

        $user = User::create([
            'name'     => trim($validated['name']),
            'email'    => strtolower(trim($validated['email'])),
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('users.index')->with('success', "Cuenta creada exitosamente para {$user->name}. Ya puede iniciar sesión.");
    }

    /**
     * Actualiza los datos o la contraseña de una cuenta de usuario.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
        ], [
            'name.required'  => 'El nombre del usuario es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email'    => 'Ingrese un formato de correo electrónico válido.',
            'email.unique'   => 'Este correo electrónico ya pertenece a otra cuenta.',
            'password.min'   => 'La nueva contraseña debe tener al menos 6 caracteres.',
        ]);

        $user->name  = trim($validated['name']);
        $user->email = strtolower(trim($validated['email']));

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', "Datos de la cuenta {$user->name} actualizados correctamente.");
    }

    /**
     * Elimina una cuenta de usuario del sistema (con protecciones de seguridad).
     */
    public function destroy(User $user)
    {
        // Protección 1: No eliminarse a sí mismo
        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')->withErrors([
                'delete_error' => 'No puedes eliminar la cuenta que estás utilizando actualmente.',
            ]);
        }

        // Protección 2: No eliminar cuenta principal de Don Ludo
        if (strcasecmp($user->email, 'DonLudo@gmail.com') === 0 || strcasecmp($user->email, 'DonLudo@gmail.chu') === 0) {
            return redirect()->route('users.index')->withErrors([
                'delete_error' => 'La cuenta de administrador principal de Don Ludo está protegida y no puede eliminarse.',
            ]);
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('users.index')->with('success', "La cuenta de {$userName} ha sido eliminada del sistema.");
    }
}
