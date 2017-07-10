<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\DTO;

use JMS\Serializer\Annotation as Serializer;

class Blueprint
{
    /**
     * @var string
     *
     * @Serializer\Type("array<string, Alberteddu\Octopus\DTO\BlueprintVariable>")
     */
    private $variables;

    /**
     * @return string
     */
    public function getVariables(): string
    {
        return $this->variables;
    }

    /**
     * @param string $variables
     *
     * @return Blueprint
     */
    public function setVariables(string $variables): Blueprint
    {
        $this->variables = $variables;

        return $this;
    }
}
