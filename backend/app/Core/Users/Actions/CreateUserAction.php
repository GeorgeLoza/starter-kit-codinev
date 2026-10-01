<?php

declare(strict_types=1);

namespace App\Core\Users\Actions;

use App\Core\Users\DTOs\CreateUserData;
use App\Models\User;

final class CreateUserAction
{
    public function execute(CreateUserData $data): User
    {
        return User::create([
            'name'     => $data->name,
            'email'    => $data->email,
            'password' => $data->password,
        ]);
    }
}
