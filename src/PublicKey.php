<?php

namespace CuongNX\LaravelAdMobSSV;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Exception;

class PublicKey
{
    protected static $publicKeyUrl = 'https://gstatic.com/admob/reward/verifier-keys.json';

    /**
     * Fetch & get PEM for a specific key_id (cache 12 hours).
     * @param int $keyId
     * @return array ['status' => bool, 'pem' => string|null, 'message' => string|null]
     * @throws Exception
     */
    public static function getPublicKeyPem(int $keyId): array
    {
        // Cache all keys map (TTL 12 hours = 43200 seconds, <24h according to AdMob docs)
        $keys = Cache::remember('admob_ssv_keys', 43200, function () {
            try {
                $client = new Client();
                $response = $client->get(self::$publicKeyUrl);

                if ($response->getStatusCode() !== 200) {
                    return [
                        'status' => false,
                        'message' => 'Failed to fetch public keys, status code: ' . $response->getStatusCode(),
                    ];
                }

                return json_decode($response->getBody()->getContents(), true)['keys'] ?? [];
            } catch (\Exception $e) {
                return [
                    'status' => false,
                    'message' => 'Error fetching public keys: ' . $e->getMessage(),
                ];
            }
        });

        if (!is_array($keys) || (isset($keys['status']) && $keys['status'] === false)) {
            return $keys;
        }

        // Find key by keyId
        foreach ($keys as $key) {
            if (Arr::get($key, 'keyId') == $keyId) {
                return [
                    'status' => true,
                    'pem' => Arr::get($key, 'pem'),
                ];
            }
        }

        // Key not found
        return [
            'status' => false,
            'message' => 'Public key not found for key_id: ' . $keyId,
        ];
    }
}
