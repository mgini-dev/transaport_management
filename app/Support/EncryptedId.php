<?php

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class EncryptedId
{
    public static function encode(int $id): string
    {
        return strtr(Crypt::encryptString((string) $id), [
            '/' => '_',
            '+' => '-',
            '=' => '.',
        ]);
    }

    public static function decode(string $encrypted): ?int
    {
        try {
            $id = Crypt::decryptString(strtr($encrypted, [
                '_' => '/',
                '-' => '+',
                '.' => '=',
            ]));
        } catch (DecryptException) {
            return null;
        }

        return is_numeric($id) ? (int) $id : null;
    }
}
