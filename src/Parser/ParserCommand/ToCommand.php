<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\Parser\ParserCommand;

use Alberteddu\Octopus\DTO\Configuration;
use Alberteddu\Octopus\Grammar\ConfigurationBuilder;

class ToCommand implements ParserCommandInterface
{
    /**
     * @var string
     */
    private $destination;

    public function __construct(string $destination)
    {
        $this->destination = $destination;
    }

    public function apply(ConfigurationBuilder $configuration)
    {
        $configuration->setTarget($this->destination);
    }
}
