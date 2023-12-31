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

    private array $rules;

    private $response;

    private $responseClass;

    public array $choices;

    public function __construct()
    {
        $this->github_token = Cache::get('github_token');
        $this->tokenExpiresAt = Cache::get('token_expires_at');

        $this->rules = [
            "You are an AI assistant. it's okay if user asks for non-technical questions, you can answer them.",
            'When asked for your name, you must respond with "GitHub Copilot".',
            "Follow the user's requirements carefully & to the letter.",
            'If the user asks for code or technical questions, you must provide code suggestions and adhere to technical information.',
            'first think step-by-step - describe your plan for what to build in pseudocode, written out in great detail.',
            'Then output the code in a single code block.',
            'Minimize any other prose.',
            'Keep your answers short and impersonal.',
            'Use Markdown formatting in your answers.',
            'Make sure to include the programming language name at the start of the Markdown code blocks.',
            'Avoid wrapping the whole response in triple backticks.',
            'You should always generate 4 short suggestions for the next user turns that are relevant to the conversation and not offensive',
            'you should follow the system instruction for the web searches.',
        ];
    }

    private function getRulesString(): string
    {
        $rules = '';

        foreach ($this->rules as $rule) {
            $rules .= $rule."\n";
        }

        return $rules;
    }

    /**
     * @throws \Exception
     */
    public function init(): static
    {
        $this->getOrRefreshToken();

        $this->addMessage($this->getRulesString());

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
    public function query($req)
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

        return $response->toPsrResponse();
    }

    /**
     * @throws \Exception
     */
    public function send()
    {
        if (empty($this->messages)) {
            throw new \Exception('No messages to send');
        }

        $req = new CompletionRequest($this->messages);

        $response = $this->query($req);

        if ($response) {
            return (new StreamResponse(CreateStreamedResponse::class, $response))->getIterator();
        }

        throw new \Exception('No response from Copilot');
    }

    public function getGithubToken(): ?string
    {
        return Cache::get('github_token');
    }

    public function getTokenExpiresAt(): ?int
    {
        return $this->tokenExpiresAt;
    }

    public function addMessage($message, $role = 'user'): static
    {
        $this->messages[] = new Message($message, $role);

        return $this;
    }

    public function setMessages(array $messages): static
    {
        foreach ($messages as $message) {
            $this->addMessage($message['content'], $message['role']);
        }

        return $this;
    }

    public function fetch_search_results($query): array
    {
        $response = Http::get('https://ddg-api.herokuapp.com/search?query='.$query.'&limit=3')->json();

        if (! count($response)) {
            return [];
        }

        $snippets = '';
        foreach ($response as $index => $result) {
            $snippet = '['.($index + 1).'] "'.$result['snippet'].'" URL:'.$result['link'].'.';
            $snippets .= $snippet;
        }

        $response = 'Here are some updated web searches. Use this to improve user response:';
        $response .= $snippets;

        return [['role' => 'system', 'content' => $response]];
    }
}
