<?php

declare(strict_types=1);

namespace App\Core\Users\Actions;

use App\Models\User;

final class DeleteUserAction
{
    public function execute(User $user): void
    {
        $user->tokens()->delete();
        $user->delete();
    }
}
