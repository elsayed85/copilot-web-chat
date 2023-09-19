<?php

namespace App\Services\Copilot;

use App\Services\Copilot\Core\CompletionRequest;
use App\Services\Copilot\Core\Response\CreateStreamedResponse;
use App\Services\Copilot\Core\Response\StreamResponse;
use App\Services\Copilot\Traits\HasMessage;
use App\Services\Copilot\Traits\HasRules;
use App\Services\Copilot\Traits\HasToken;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\ResponseInterface;

class CopilotApi
{
    use HasMessage , HasRules, HasToken;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setGithubToken(auth()->user()->getGithubToken());
        $this->getOrRefreshToken();
        $this->prepareRules();
    }

    private function prepareRules(): void
    {
        $this->setRules([
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
        ]);

        $this->addMessage($this->getRulesString(), 'system');
    }

    /**
     * @throws \Exception
     */
    private function query($req): ResponseInterface
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

            throw new \Exception("[$code] $message");
        }

        return $response->toPsrResponse();
    }

    /**
     * @throws \Exception
     */
    public function send(): iterable
    {
        if (empty($this->messages)) {
            throw new \Exception('No messages to send');
        }

        $req = new CompletionRequest($this->messages);

        $response = $this->query($req);

        return (new StreamResponse(CreateStreamedResponse::class, $response))->getIterator();
    }
}
