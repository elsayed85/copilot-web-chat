<x-filament-widgets::widget>
    <x-filament::section>
        @if($isAuthenticated)
            <x-filament::section aside>
                <x-slot name="heading">
                    Github Authentication
                </x-slot>

                <x-slot name="description">
                    You are authenticated with github.
                </x-slot>

                <x-filament::button wire:click="logoutGithubAccount" color="danger">
                    Logout
                </x-filament::button>
            </x-filament::section>
        @else
            <x-filament::section aside>
                <x-slot name="heading">
                    Github Authentication (First Step)
                </x-slot>

                <x-slot name="description">
                    Authenticate your github account to use the github integration.
                </x-slot>

                <x-filament::button wire:click="generateGithubAuthCode">
                    Generate Code
                </x-filament::button>
            </x-filament::section>

            @if($authCode)
                <x-filament::section aside>
                    <x-slot name="heading">
                        Github Authentication Code (Second Step)
                    </x-slot>

                    <x-slot name="description">
                        use this code to authenticate your account on github: <strong>{{ $authCode }}</strong>
                    </x-slot>

                    <x-filament::button href="https://github.com/login/device" target="_blank" tag="a">
                        Github Auth Page
                    </x-filament::button>
                </x-filament::section>

                <x-filament::section aside>
                    <x-slot name="heading">
                        Confirm Authentication (Third Step)
                    </x-slot>

                    <x-slot name="description">
                        after authenticating your account, click the button below to complete the process
                    </x-slot>

                    <x-filament::button wire:click="confirmGithubAuth">
                        Confirm Authentication
                    </x-filament::button>
                </x-filament::section>

            @endif
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
