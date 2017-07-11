<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\DTO;

class BlueprintInstance
{
    /**
     * @var Blueprint
     */
    private $blueprint;

    /**
     * @var array
     */
    private $instance;

    public function __construct(Blueprint $blueprint, array $instance)
    {
        $this->blueprint = $blueprint;
        $this->instance = $instance;
    }

    public function __get($name)
    {
        if (!$this->blueprint->variableExists($name)) {
            return null;
        }

        if (isset($this->instance[$name])) {
            return $this->instance[$name];
        }

        return $this->blueprint->getVariable($name)->getDefault();
    }

    function __isset($name)
    {
        return $this->blueprint->variableExists($name);
    }
}
