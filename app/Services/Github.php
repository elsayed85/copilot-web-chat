<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Github
{
    public function auth(): array
    {
        $response = Http::asForm()->post(
            'https://github.com/login/device/code',
            [
                'client_id' => config('github-copilot-chat.client_id'),
                'scope' => 'user:email',
            ]
        )->body();

        $response = explode('&', $response);
        $user_code = explode('=', $response[3])[1];
        $device_code = explode('=', $response[0])[1];

        Cache::put('user_code', $user_code, 60 * 60 * 24);
        Cache::put('device_code', $device_code, 60 * 60 * 24);

        return [
            'user_code' => $user_code,
            'device_code' => $device_code,
        ];
    }

    public function check(): bool
    {
        $device_code = Cache::get('device_code');

        if (! $device_code) {
            return false;
        }

        $response = Http::asForm()->post(
            'https://github.com/login/oauth/access_token',
            [
                'client_id' => config('github-copilot-chat.client_id'),
                'scope' => 'user:email',
                'device_code' => $device_code,
                'grant_type' => 'urn:ietf:params:oauth:grant-type:device_code',
            ]
        );

        $response = explode('&', $response->body());

        $resp = explode('=', $response[0]);
        $key = $resp[0] ?? null;
        $access_token = $resp[1] ?? null;

        if (! $access_token) {
            return false;
        }

        if ($key == 'error') {
            return false;
        }

        if ($access_token == 'authorization_pending') {
            return false;
        }

        Cache::forever('github_token', $access_token);
        Cache::forget('user_code');
        Cache::forget('device_code');

        return true;
    }
}
