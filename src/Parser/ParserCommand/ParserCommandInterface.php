<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\Parser\ParserCommand;

use Alberteddu\Octopus\Grammar\ConfigurationBuilder;

interface ParserCommandInterface
{
    public function apply(ConfigurationBuilder $configuration);
}
