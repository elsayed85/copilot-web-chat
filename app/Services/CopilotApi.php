<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CopilotApi
{
    private ?string $github_token;

    private ?string $token;

    private ?int $tokenExpiresAt;

    private array $messages = [];

    public function __construct()
    {
        $this->github_token = Cache::get('github_token');
        $this->tokenExpiresAt = Cache::get('token_expires_at');
    }

    /**
     * @throws \Exception
     */
    public function init(): static
    {
        $this->getOrRefreshToken();

        return $this;
    }

    public function getExpiresIn(): string
    {
        return Carbon::createFromTimestamp($this->tokenExpiresAt)->diff(Carbon::now())->format('%H:%I:%S');
    }

    public function hasGithubToken(): bool
    {
        return is_string($this->github_token) && ! empty($this->github_token);
    }

    public function tokenExpired(): bool
    {
        return $this->tokenExpiresAt && $this->tokenExpiresAt < Carbon::now()->timestamp;
    }

    /**
     * @throws \Exception
     */
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

    public function getOrRefreshToken(): void
    {
        if ($this->tokenExpiresAt < Carbon::now()->timestamp) {
            $response = $this->generateToken();

            if (! isset($response['token']) || ! isset($response['expires_at'])) {
                throw new \Exception('Could not generate token');
            }

            $this->token = $response['token'];
            $this->tokenExpiresAt = $response['expires_at'];

            Cache::put('copilot_token', $this->token, $this->tokenExpiresAt);
            Cache::put('token_expires_at', $this->tokenExpiresAt, $this->tokenExpiresAt);
        } else {
            $this->token = Cache::get('copilot_token');
        }
    }

    public function logout(): void
    {
        Cache::forget('github_token');
        Cache::forget('copilot_token');
        Cache::forget('token_expires_at');
    }

    /**
     * @throws \Exception
     */
    public function query($req): array
    {
        $response = Http::withHeaders([
            'User-Agent' => config('github-copilot-chat.user_agent'),
        ])
            ->asJson()
            ->withToken($this->token)
            ->post('https://copilot-proxy.githubusercontent.com/v1/chat/completions', $req);

        if ($response->status() != 200) {
            $error = $response->json()['error'];
            $code = strtoupper($error['code']);
            $message = $error['message'];

            return [
                'type' => 'error',
                'text' => "Error: $code - $message",
            ];
        }

        $data = $response->body();

        return [
            'type' => 'success',
            'text' => $this->getFinalString($data),
        ];
    }

    /**
     * @throws \Exception
     */
    public function send(): string
    {
        if (empty($this->messages)) {
            throw new \Exception('No messages to send');
        }

        $req = new CompletionRequest($this->messages);

        $response = $this->query($req);

        if ($response) {
            return $response['text'];
        }

        throw new \Exception('No response from Copilot');
    }

    public function getGithubToken(): ?string
    {
        return Cache::get('github_token');
    }

    public function getFinalString(string $data): string
    {
        $dataset = explode("\n", $data);
        $final_string = '';
        foreach ($dataset as $str) {
            $line = substr($str, 6);
            if (str_contains($str, 'data: ')) {

                $responseData = json_decode($line, true);

                $choices = $responseData['choices'] ?? null;

                if ($choices) {
                    foreach ($choices as $choice) {
                        $delta = $choice['delta'] ?? null;

                        if ($delta) {
                            $final_string .= $delta['content'] ?? '';
                        }
                    }
                }
            }
        }

        return $final_string;
    }

    public function getTokenExpiresAt(): ?int
    {
        return $this->tokenExpiresAt;
    }

    public function setMessages(array $messages): static
    {
        foreach ($messages as $message) {
            $this->messages[] = new Message($message['content'], $message['role']);
        }

        return $this;
    }
}
