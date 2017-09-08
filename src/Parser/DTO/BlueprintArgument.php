<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\Parser\DTO;

class BlueprintArgument
{
    private $name;

    private $value;

    public static function create(string $name, string $value)
    {
        $instance = new self;
        $instance->name = $name;
        $instance->value = $value;

        return $instance;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
