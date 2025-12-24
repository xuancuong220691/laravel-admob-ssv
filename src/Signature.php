<?php

namespace CuongNX\LaravelAdMobSSV;

use Illuminate\Http\Request;

class Signature
{
    /**
     * Create binary signature from request input.
     * @param Request $request
     * @return string Binary signature
     */
    public static function createFromRequest(Request $request): string
    {
        $signature = $request->input('signature');

        $signature = str_replace(['-', '_'], ['+', '/'], $signature);
        $signature .= str_repeat('=', (4 - strlen($signature) % 4) % 4);

        $binary = base64_decode($signature);
        if ($binary === false) {
            throw new \Exception('Invalid signature base64');
        }
        return $binary;
    }
}
