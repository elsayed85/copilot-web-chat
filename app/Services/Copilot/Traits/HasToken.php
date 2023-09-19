<?php

namespace App\Services\Copilot\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

trait HasToken
{
    use HasGithubToken;

    private ?string $token;

    private ?int $tokenExpiresAt;

    public function generateToken()
    {
        $response = Http::withHeaders([
            'User-Agent' => config('github-copilot-chat.user_agent'),
        ])
            ->withToken($this->getGithubToken())
            ->get('https://api.github.com/copilot_internal/v2/token');

        if ($response->status() != 200) {
            throw new \Exception('Could not generate token');
        }

        return $response->json();
    }

    private function shouldGenerateNewToken(): bool
    {
        return $this->tokenExpiresAt < Carbon::now()->timestamp;
    }

    /**
     * @throws \Exception
     */
    public function getOrRefreshToken(): void
    {
        if ($this->shouldGenerateNewToken()) {
            $response = $this->generateToken();

            if (! isset($response['token']) || ! isset($response['expires_at'])) {
                throw new \Exception('Could not generate token');
            }

            auth()->user()->saveCopilotToken($this->token, $this->tokenExpiresAt);
        }

        $this->token = auth()->user()->getCopilotToken();
        $this->tokenExpiresAt = auth()->user()->getCopilotTokenExpiresAt();
    }
}
