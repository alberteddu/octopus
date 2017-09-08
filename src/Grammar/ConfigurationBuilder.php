<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\Grammar;

use Alberteddu\Octopus\Parser\DTO\BlueprintArgument;
use Alberteddu\Octopus\Parser\DTO\BlueprintVariable;

class ConfigurationBuilder
{
    private $configuration;

    private function __construct()
    {
        $this->configuration = ['output' => []];
    }

    public function setTarget(string $target)
    {
        $this->configuration['target'] = $target;
    }

    public function addData($name, $arguments)
    {
        if (!isset($this->configuration['data'])) {
            $this->configuration['data'] = [];
        }

        if (!isset($this->configuration['data'][$name])) {
            $this->configuration['data'][$name] = [];
        }

        $argumentsArray = [];

        /** @var BlueprintArgument $argument */
        foreach ($arguments as $argument) {
            $argumentsArray[$argument->getName()] = $argument->getValue();
        }

        $this->configuration['data'][$name][] = $argumentsArray;
    }

    public function addBlueprint($name)
    {
        if (!isset($this->configuration['blueprints'])) {
            $this->configuration['blueprints'] = [];
        }

        $this->configuration['blueprints'][$name] = [
            'variables' => [],
        ];
    }

    public function addBlueprintVariable($blueprint, $name, $type, $required = false, $default = null)
    {
        $newVariable = [
            'type' => $type,
            'required' => $required
        ];

        if (null !== $default) {
            $newVariable['default'] = $default;
        }

        $this->configuration['blueprints'][$blueprint]['variables'][$name] = $newVariable;
    }

    public function addBlueprintVariableObject($blueprint, BlueprintVariable $blueprintVariable)
    {
        $this->addBlueprintVariable(
            $blueprint,
            $blueprintVariable->getName(),
            $blueprintVariable->getType(),
            $blueprintVariable->isRequired(),
            $blueprintVariable->getDefault()
        );
    }

    public static function create(): self
    {
        return new self;
    }

    public function asJson(): string
    {
        return json_encode($this->configuration);
    }
}
