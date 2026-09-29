<?php

declare(strict_types=1);

namespace App\Core\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class LoginAction
{
    public function execute(string $email, string $password, string $device = 'web'): array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no coinciden con nuestros registros.'],
            ]);
        }

        // Revoca tokens previos del mismo dispositivo (opcional, controlado)
        // $user->tokens()->where('name', $device)->delete();

        $token = $user->createToken($device)->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }
}
