<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query()->with('roles');

        if ($request->filled('rol')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $request->string('rol')));
        }

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->string('estatus'));
        }

        if ($request->filled('busqueda')) {
            $busqueda = $request->string('busqueda');
            $query->where(fn ($q) => $q->where('name', 'like', "%{$busqueda}%")
                ->orWhere('email', 'like', "%{$busqueda}%"));
        }

        return $query->latest()->get();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());
        return response()->json(['message' => 'Usuario creado correctamente', 'usuario' => $user], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return $user;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());
        return response()->json(['message'=>'Usuario actualizado correctamente','usuario'=>$user],201);
    }

    /**
     * Deshabilita al usuario (no se elimina de la base de datos).
     * Para reactivarlo: PUT/PATCH /api/usuarios/{usuario} con estatus=activo.
     */
    public function destroy(User $user)
    {
        $user->update(['estatus' => 'suspendido']);
        return response()->json(['message' => 'Usuario deshabilitado correctamente']);
    }
}
