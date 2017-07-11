<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\DTO;

use JMS\Serializer\Annotation as Serializer;

class Blueprint
{
    /**
     * @var BlueprintVariable[]
     *
     * @Serializer\Type("array<string, Alberteddu\Octopus\DTO\BlueprintVariable>")
     */
    private $variables;

    /**
     * @return BlueprintVariable[]
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    public function variableExists(string $name): bool
    {
        return isset($this->variables[$name]);
    }

    /**
     * @param array $variables
     *
     * @return Blueprint
     */
    public function setVariables(array $variables): Blueprint
    {
        $this->variables = $variables;

        return $this;
    }

    public function getVariable(string $name): ?BlueprintVariable
    {
        if (!$this->variableExists($name)) {
            return null;
        }

        return $this->variables[$name];
    }
}
