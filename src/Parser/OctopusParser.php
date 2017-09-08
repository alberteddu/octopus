<?php

namespace Alberteddu\Octopus\Parser;

use Alberteddu\Octopus\Grammar\ConfigurationBuilder;
use Alberteddu\Octopus\Grammar\OctopusGrammar;
use Alberteddu\Octopus\Parser\ParserCommand\ParserCommandInterface;

class OctopusParser
{
    public static function parse($contents): string
    {
        $commands = OctopusGrammar::getGrammar()->parse($contents);
        $configuration = ConfigurationBuilder::create();

        /** @var ParserCommandInterface $command */
        foreach ($commands as $command) {
            $command->apply($configuration);
        }

        var_dump($configuration);

        return $configuration->asJson();
    }
}


