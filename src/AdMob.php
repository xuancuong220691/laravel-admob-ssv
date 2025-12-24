<?php

namespace CuongNX\LaravelAdMobSSV;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdMob
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Verify callback from AdMob.
     * @return array
     */
    public function validate()
    {
        $validator = \Illuminate\Support\Facades\Validator::make($this->request->all(), [
            'key_id' => 'required|integer',
            'signature' => 'required|string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            Log::warning('AdMob SSV: Missing or invalid parameters', ['errors' => $errors, 'request' => $this->request->all()]);
            return [
                'status' => false,
                'message' => 'Missing or invalid parameters',
                'errors' => $errors,
            ];
        }

        $publicKeyPem = PublicKey::getPublicKeyPem((int)$this->request->input('key_id'));
        if ($publicKeyPem['status'] === false) {
            $message = $publicKeyPem['message'] ?? 'Unknown error fetching public key';
            Log::error('AdMob SSV: Error fetching public key', ['message' => $message, 'request' => $this->request->all()]);
            return [
                'status' => false,
                'message' => $message,
            ];
        }

        $signature = Signature::createFromRequest($this->request);

        $message = collect($this->request->except(['key_id', 'signature']))
            ->map(function ($value, $key) {
                return "{$key}={$value}";
            })
            ->implode('&');

        $publicKey = openssl_pkey_get_public($publicKeyPem['pem']);
        if (!$publicKey) {
            $errorMessage = 'Invalid public key: ' . openssl_error_string();
            Log::error('AdMob SSV: ' . $errorMessage, ['request' => $this->request->all()]);
            return [
                'status' => false,
                'message' => $errorMessage,
            ];
        }

        $verifyResult = openssl_verify($message, $signature, $publicKey, OPENSSL_ALGO_SHA256);

        if (PHP_VERSION_ID < 80000) {
            openssl_free_key($publicKey);
        }

        if ($verifyResult !== 1) {
            $errorMessage = 'Verification failed: ' . ($verifyResult === 0 ? 'Invalid signature' : openssl_error_string());
            Log::error('AdMob SSV: ' . $errorMessage, ['request' => $this->request->all()]);
            return [
                'status' => false,
                'message' => $errorMessage,
            ];
        }

        Log::info('AdMob SSV: Verification successful', ['request' => $this->request->all()]);

        return [
            'status' => true,
            'message' => 'Verification successful',
        ];
    }

    /**
     * Check if validation failed.
     * @return bool
     */
    public function failed()
    {
        $validation = $this->validate();
        return !$validation['status'];
    }
}