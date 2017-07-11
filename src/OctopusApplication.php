<?php

declare(strict_types=1);

namespace Alberteddu\Octopus;

use Alberteddu\Octopus\Command\BuildCommand;
use Deployer\Component\PharUpdate\Console\Command;
use Deployer\Component\PharUpdate\Console\Helper;
use Symfony\Component\Console\Application;

class OctopusApplication extends Application
{
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);

        $this->add(new BuildCommand());

        $command = new Command('update');
        $command->setManifestUri('https://alberteddu.github.io/octopus/manifest.json');
        $this->getHelperSet()->set(new Helper());
        $this->add($command);
    }
}
