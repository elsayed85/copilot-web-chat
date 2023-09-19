<?php

namespace App\Filament\User\Widgets;

use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class GithubAuthWidget extends Widget
{
    protected static string $view = 'filament.user.widgets.github-auth-widget';

    protected int|string|array $columnSpan = 12;

    protected string $code;

    protected User $user;

    protected bool $isAuthenticated;

    public function __construct()
    {
        $this->user = auth()->user();
        $this->code = '';
        $this->isAuthenticated = $this->checkIfAuthenticated();
    }

    protected function getViewData(): array
    {
        return [
            'isAuthenticated' => $this->isAuthenticated,
            'authCode' => $this->code,
        ];
    }

    private function checkIfAuthenticated(): bool
    {
        return $this->user->hasGithubToken();
    }

    public function logoutGithubAccount(): void
    {
        $this->user->githubLogout();
    }

    public function generateGithubAuthCode(): void
    {
        $this->code = $this->user->generateGithubToken();
    }

    public function confirmGithubAuth(): void
    {
        $this->isAuthenticated = $this->user->confirmGithubToken();

        if ($this->isAuthenticated) {
            Notification::make()
                ->title('Github account authenticated')
                ->body('Your Github account has been authenticated successfully')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Github account not authenticated')
                ->body('Your Github account could not be authenticated')
                ->danger()
                ->send();
        }
    }
}
