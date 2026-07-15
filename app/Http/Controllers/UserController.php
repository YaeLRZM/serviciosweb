<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyUserRequest;
use App\Http\Requests\IndexUserRequest;
use App\Http\Requests\ShowUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;

class UserController extends Controller
{
    public function index(IndexUserRequest $request)
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

    public function store(StoreUserRequest $request)
    {
        return User::create($request->validated());
    }

    public function show(ShowUserRequest $request, User $usuario)
    {
        return $usuario->load('roles');
    }

    public function update(UpdateUserRequest $request, User $usuario)
    {
        $data = $request->validated();

        $rol = $data['rol'] ?? null;
        unset($data['rol']);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $usuario->update($data);

        if ($rol) {
            $usuario->syncRoles([$rol]);
        }

        return $usuario->load('roles');
    }

    public function destroy(DestroyUserRequest $request, User $usuario)
    {
        $usuario->delete();

        return response()->noContent();
    }
}
