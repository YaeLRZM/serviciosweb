<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Models\User;
use App\Services\JsonStorageService;
use Illuminate\Support\Facades\Hash;

class JsonUserProvider implements UserProvider
{
    protected $storage;

    public function __construct()
    {
        $this->storage = new JsonStorageService();
    }

    public function retrieveById($id)
    {
        $users = $this->storage->read('users.json');

        foreach ($users as $userData) {
            if ($userData['id'] == $id) {
                return User::fromArray($userData);
            }
        }

        return null;
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials)) {
            return null;
        }

        $users = $this->storage->read('users.json');

        foreach ($users as $userData) {
            if (($userData['nombre'] ?? null) === ($credentials['nombre'] ?? null)) {
                return User::fromArray($userData);
            }
        }

        return null;
    }

    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // Not used for JSON storage
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return Hash::check(
            $credentials['password'] ?? '',
            $user->getAuthPassword()
        );
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, $force = false)
    {
        // Not used for JSON storage
    }
}
