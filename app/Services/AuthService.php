<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    /**
     * Used when the email does not exist, so the check takes about the same time.
     */
    private const UNKNOWN_USER_PASSWORD_HASH = '$2y$12$hMspuIewY5ctGSxdt9ibPOg0MXZpYlB5yo6RwuV3zzJKYBZe7OAM6';

    public function authenticate(string $email, string $password): User
    {
        $user = $this->findByEmail($email);
        $passwordMatches = $this->passwordMatches($user, $password);

        if (! $user instanceof User || ! $passwordMatches) {
            throw ValidationException::withMessages([
                'email' => ['The email or the password is wrong.'],
            ]);
        }

        return $user;
    }

    /**
     * @return array{user: User, token: string}
     */
    public function login(string $email, string $password): array
    {
        $user = $this->authenticate($email, $password);

        return [
            'user' => $user,
            'token' => $this->issueToken($user),
        ];
    }

    private function issueToken(User $user): string
    {
        return $user->createToken('api')->plainTextToken;
    }

    /**
     * Deletes only the token of this request.
     */
    public function logout(mixed $user): void
    {
        if (! $user instanceof User) {
            return;
        }

        $token = $user->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }

    private function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    /**
     * The hash check also runs when the email does not exist.
     */
    private function passwordMatches(?User $user, string $password): bool
    {
        return Hash::check($password, $user?->password ?? self::UNKNOWN_USER_PASSWORD_HASH);
    }
}
