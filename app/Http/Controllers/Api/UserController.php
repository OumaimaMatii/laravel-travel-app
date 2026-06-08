<?php
// app/Http/Controllers/Api/UserController.php
// Contrôleur admin pour la gestion des utilisateurs (CRUD complet)

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * GET /admin/users — Liste tous les utilisateurs
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filtre par rôle
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Recherche par nom ou email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('role')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data'    => $users,
            'total'   => $users->count(),
            'stats'   => [
                'total'   => User::count(),
                'admins'  => User::where('role', 'admin')->count(),
                'agents'  => User::where('role', 'agent')->count(),
                'clients' => User::where('role', 'client')->count(),
            ],
        ]);
    }

    /**
     * POST /admin/users — Crée un nouvel utilisateur
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => ['required', Rule::in(['client', 'agent', 'admin'])],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur créé avec succès.',
            'data'    => $user,
        ], 201);
    }

    /**
     * GET /admin/users/{id} — Détail d'un utilisateur
     */
    public function show($id)
    {
        $user = User::with(['reservations', 'voyagesForfait', 'voyagesSurMesure'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $user,
        ]);
    }

    /**
     * PUT /admin/users/{id} — Mise à jour d'un utilisateur
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => ['sometimes', 'email', Rule::unique('users')->ignore($id)],
            'password' => 'sometimes|nullable|string|min:8',
            'role'     => ['sometimes', Rule::in(['client', 'agent', 'admin'])],
        ]);

        $data = $request->only(['name', 'email', 'role']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur mis à jour.',
            'data'    => $user->fresh(),
        ]);
    }

    /**
     * DELETE /admin/users/{id} — Supprime un utilisateur
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Sécurité : ne pas supprimer le dernier admin
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer le dernier administrateur.',
            ], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur supprimé.',
        ]);
    }
}