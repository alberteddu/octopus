<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\Parser\DTO;

class BlueprintVariable
{
    private $name;

    private $type;

    private $required = false;

    private $default = null;

    public static function create(string $name, string $type, bool $required = false, $default = null)
    {
        $instance = new self;
        $instance->name = $name;
        $instance->type = $type;
        $instance->required = $required;
        $instance->default = $default;

        return $instance;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getDefault()
    {
        return $this->default;
    }
}
