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
        return User::all();
    }

    public function store(StoreUserRequest $request)
    {
        return User::create($request->validated());
    }

    public function show(ShowUserRequest $request, User $usuario)
    {
        return $usuario;
    }

    public function update(UpdateUserRequest $request, User $usuario)
    {
        $usuario->update($request->validated());

        return $usuario;
    }

    public function destroy(DestroyUserRequest $request, User $usuario)
    {
        $usuario->delete();

        return response()->noContent();
    }
}
