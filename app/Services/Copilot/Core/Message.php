<?php

namespace App\Services\Copilot\Core;

class Message
{
    public function __construct(public string $content, public string $role)
    {
        //
    }
}
