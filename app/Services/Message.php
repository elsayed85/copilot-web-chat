<?php

namespace App\Services;

class Message
{
    public function __construct(public string $content, public string $role)
    {
        //
    }
}
