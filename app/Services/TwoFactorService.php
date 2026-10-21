<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Time-based one-time passwords (RFC 6238, SHA-1, 6 digits, 30 second steps)
 * with hashed single-use recovery codes. Implemented directly so the starter
 * kit needs no extra dependency.
 */
class TwoFactorService
{
    /** Session key holding the user awaiting their second factor during sign-in. */
    public const CHALLENGE_SESSION_KEY = 'two_factor.login';

    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    private const DIGITS = 6;

    private const PERIOD = 30;

    private const RECOVERY_CODE_COUNT = 8;

    /**
     * How many 30-second steps either side of "now" are accepted, to absorb clock drift.
     */
    private const WINDOW = 1;

    public function generateSecret(): string
    {
        return $this->base32Encode(random_bytes(20));
    }

    public function provisioningUri(User $user, string $secret): string
    {
        $issuer = rawurlencode((string) config('app.name'));
        $label = rawurlencode($issuer.':'.$user->email);

        return "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=".self::DIGITS.'&period='.self::PERIOD;
    }

    /**
     * Starts (or restarts) setup. The secret is stored but not enforced until confirmed.
     */
    public function beginSetup(User $user): string
    {
        $secret = $this->generateSecret();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return $secret;
    }

    /**
     * Confirms setup with a code from the authenticator app.
     *
     * @return array<int, string>|null The plain recovery codes (shown once), or null when the code is wrong.
     */
    public function confirm(User $user, string $code): ?array
    {
        if ($user->two_factor_secret === null || ! $this->verifyTotp($user->two_factor_secret, $code)) {
            return null;
        }

        $codes = $this->newRecoveryCodes();

        $user->forceFill([
            'two_factor_recovery_codes' => array_map(fn (string $code) => $this->hashRecoveryCode($code), $codes),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $codes;
    }

    /**
     * Whether the sign-in may complete: always for users without two-factor,
     * otherwise only with a valid code or recovery code.
     */
    public function verifyLogin(User $user, ?string $code): bool
    {
        if (! $user->hasTwoFactorEnabled()) {
            return true;
        }

        return filled($code) && $this->verifyChallenge($user, $code);
    }

    /**
     * Accepts either a current TOTP code or an unused recovery code. A recovery
     * code is consumed on success so it cannot be replayed.
     */
    public function verifyChallenge(User $user, string $input): bool
    {
        $input = trim($input);

        if ($user->two_factor_secret !== null && preg_match('/^\d{6}$/', $input) === 1) {
            return $this->verifyTotp($user->two_factor_secret, $input);
        }

        $hashes = $user->two_factor_recovery_codes ?? [];
        $index = array_search($this->hashRecoveryCode($input), $hashes, true);

        if ($index === false) {
            return false;
        }

        unset($hashes[$index]);

        $user->forceFill(['two_factor_recovery_codes' => array_values($hashes)])->save();

        return true;
    }

    /**
     * @return array<int, string>
     */
    public function regenerateRecoveryCodes(User $user): array
    {
        $codes = $this->newRecoveryCodes();

        $user->forceFill([
            'two_factor_recovery_codes' => array_map(fn (string $code) => $this->hashRecoveryCode($code), $codes),
        ])->save();

        return $codes;
    }

    public function disable(User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }

    public function verifyTotp(string $secret, string $code, ?int $timestamp = null): bool
    {
        $timestamp ??= time();

        for ($step = -self::WINDOW; $step <= self::WINDOW; $step++) {
            if (hash_equals($this->totp($secret, $timestamp + $step * self::PERIOD), trim($code))) {
                return true;
            }
        }

        return false;
    }

    public function totp(string $secret, int $timestamp): string
    {
        $counter = intdiv($timestamp, self::PERIOD);
        $hash = hash_hmac('sha1', pack('J', $counter), $this->base32Decode($secret), true);

        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $binary = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($binary % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * @return array<int, string>
     */
    private function newRecoveryCodes(): array
    {
        return collect(range(1, self::RECOVERY_CODE_COUNT))
            ->map(fn () => Str::lower(Str::random(5)).'-'.Str::lower(Str::random(5)))
            ->all();
    }

    private function hashRecoveryCode(string $code): string
    {
        return hash('sha256', Str::lower(trim($code)));
    }

    private function base32Encode(string $bytes): string
    {
        $bits = '';

        foreach (str_split($bytes) as $char) {
            $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $encoded = '';

        foreach (str_split($bits, 5) as $chunk) {
            $encoded .= self::BASE32_ALPHABET[bindec(str_pad($chunk, 5, '0'))];
        }

        return $encoded;
    }

    private function base32Decode(string $secret): string
    {
        $bits = '';

        foreach (str_split(strtoupper($secret)) as $char) {
            $value = strpos(self::BASE32_ALPHABET, $char);

            if ($value === false) {
                throw new InvalidArgumentException('Invalid base32 character in two-factor secret.');
            }

            $bits .= str_pad(decbin($value), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';

        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $bytes .= chr(bindec($chunk));
            }
        }

        return $bytes;
    }
}
