<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Register
    public function register(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cognome' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            'eta' => 'required|string',
            'telefono' => 'required|string',
            'indirizzo' => 'required|string',
        ]);

        $user = User::create([
            'nome' => $request->nome,
            'cognome' => $request->cognome,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'eta' => $request->eta,
            'telefono' => $request->telefono,
            'indirizzo' => $request->indirizzo,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registrazione avvenuta con successo',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    // Login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenziali non valide.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login avvenuto con successo',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    // Info utente
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout avvenuto con successo'
        ]);
    }

 public function updateProfile(Request $request)
{
    $user = $request->user(); 

    if (!$user) {
        return response()->json([
            'error' => true,
            'message' => 'Utente non autenticato'
        ], 401);
    }

    // Validation
    $request->validate([
        'nome' => 'sometimes|string|max:255',
        'cognome' => 'sometimes|string|max:255',
        'email' => 'sometimes|email|unique:users,email,' . $user->id,
        'eta' => 'sometimes|integer|min:10|max:100',
        'password' => 'sometimes|string|min:6',
        'telefono' => 'sometimes|string',
        'indirizzo' => 'sometimes|string',
    ]);

    // Remplir automatiquement les champs
    $user->fill($request->only(['nome','cognome','email','eta','telefono','indirizzo']));

    // Hasher le mot de passe si présent
    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    return response()->json([
        'message' => 'Profilo aggiornato con successo',
        'user' => $user
    ]);
}


}
