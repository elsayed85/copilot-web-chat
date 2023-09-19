<?php

namespace App\Services\Copilot\Traits;

trait HasGithubToken
{
    private string $githubToken;

    public function setGithubToken(string $githubToken): static
    {
        $this->githubToken = $githubToken;

        return $this;
    }

    public function getGithubToken(): string
    {
        return $this->githubToken;
    }
}
