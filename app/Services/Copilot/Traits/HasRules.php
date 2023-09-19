<?php

namespace App\Services\Copilot\Traits;

trait HasRules
{
    private array $rules;

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    private function getRulesString(): string
    {
        $rules = '';

        foreach ($this->rules as $rule) {
            $rules .= $rule."\n";
        }

        return $rules;
    }
}
