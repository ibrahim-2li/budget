<?php

namespace App\Models;

use Database\Factories\BiometricTokenFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A device token that signs its user in after a fingerprint match in the Android app.
 * Only the SHA-256 hash is stored; the plain token lives encrypted on the phone.
 */
#[Fillable(['user_id', 'token_hash', 'last_used_at'])]
#[Hidden(['token_hash'])]
class BiometricToken extends Model
{
    /** @use HasFactory<BiometricTokenFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
        ];
    }

    /**
     * The user this token signs in.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Issue a new token for the user and return its plain value.
     */
    public static function issueFor(User $user): string
    {
        $token = Str::random(64);

        $user->biometricTokens()->create(['token_hash' => self::hash($token)]);

        return $token;
    }

    /**
     * Find the token record matching a plain token.
     */
    public static function findByToken(string $token): ?self
    {
        return self::where('token_hash', self::hash($token))->first();
    }

    public static function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
