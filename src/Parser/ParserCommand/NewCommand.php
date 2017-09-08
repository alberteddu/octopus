<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\Parser\ParserCommand;

use Alberteddu\Octopus\DTO\Configuration;
use Alberteddu\Octopus\Grammar\ConfigurationBuilder;

class NewCommand implements ParserCommandInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $arguments;

    public function __construct(string $name, array $arguments)
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }

    public function apply(ConfigurationBuilder $configuration)
    {
        $configuration->addData($this->name, $this->arguments);
    }
}
