<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/register
     * Response: { message, userId }
     */
    public function register(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:users,email',
            'no_telp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        $user = User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'no_telp' => $request->no_telp ?? '',
            'alamat' => $request->alamat ?? '',
            'username' => $request->username,
            'password' => $request->password, // auto-hashed via cast
            'role' => 'USER',
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil!',
            'userId' => $user->id_user,
        ], 201);
    }

    /**
     * POST /api/login
     * Body: { identifier, password }
     * Response: { token, user: { id, nama, role } }
     */
    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string',
        ], [
            'identifier.required' => 'Email/username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Cari user by email ATAU username
        $user = User::where('email', $request->identifier)
            ->orWhere('username', $request->identifier)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'Email/username atau password salah.',
            ], 401);
        }

        // Buat Sanctum token
        $token = $user->createToken('flutter-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id_user,
                'nama' => $user->nama,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * POST /api/logout (auth:sanctum)
     * Revoke token yang sedang digunakan
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout.',
        ]);
    }

    /**
     * GET /api/user (auth:sanctum)
     * Return data user yang sedang login
     */
    public function user(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id_user,
            'nama' => $user->nama,
            'email' => $user->email,
            'no_telp' => $user->no_telp,
            'alamat' => $user->alamat,
            'username' => $user->username,
            'foto_profil' => $user->foto_profil,
            'role' => $user->role,
            'created_at' => $user->created_at,
        ]);
    }
}
