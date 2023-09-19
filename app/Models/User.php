<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\Copilot\Github;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'copilot_token_expires_at' => 'datetime',
    ];

    public function generateGithubToken(): string
    {
        $github = new Github();
        $data = $github->generateToken();
        $this->update([
            'github_user_code' => $data['user_code'],
            'github_device_code' => $data['device_code'],
        ]);

        return $data['user_code'];
    }

    public function confirmGithubToken(): bool
    {
        $github = new Github();
        $token = $github->confirm($this->github_device_code);

        if (! $token) {
            return false;
        }

        $this->update([
            'github_token' => $token,
        ]);

        return true;
    }

    public function hasGithubToken(): bool
    {
        return ! empty($this->github_token);
    }

    public function getGithubToken(): ?string
    {
        return $this->github_token;
    }

    public function githubLogout(): void
    {
        $this->update([
            'github_token' => null,
            'github_user_code' => null,
            'github_device_code' => null,
        ]);
    }

    public function saveCopilotToken($token, $expires_at): void
    {
        $this->update([
            'copilot_token' => $token,
            'copilot_token_expires_at' => $expires_at,
        ]);
    }

    public function getCopilotToken(): ?string
    {
        return $this->copilot_token;
    }

    public function getCopilotTokenExpiresAt(): ?string
    {
        return $this->copilot_token_expires_at;
    }
}
