<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\Parser\ParserCommand;

use Alberteddu\Octopus\DTO\Configuration;
use Alberteddu\Octopus\Grammar\ConfigurationBuilder;

class DefineCommand implements ParserCommandInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $variables;

    public function __construct(string $name, array $variables)
    {
        $this->name = $name;
        $this->variables = $variables;
    }

    public function apply(ConfigurationBuilder $configuration)
    {
        $configuration->addBlueprint($this->name);

        foreach ($this->variables as $variable) {
            $configuration->addBlueprintVariableObject($this->name, $variable);
        }
    }
}
