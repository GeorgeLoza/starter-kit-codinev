<?php

declare(strict_types=1);

namespace App\Core\Users\Actions;

use App\Core\Users\DTOs\UpdateUserData;
use App\Models\User;

final class UpdateUserAction
{
    public function execute(User $user, UpdateUserData $data): User
    {
        $user->update($data->toArray());

        return $user->refresh();
    }
}
