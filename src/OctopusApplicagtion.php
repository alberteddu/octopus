<?php

declare(strict_types=1);

namespace Alberteddu\Octopus;

use Alberteddu\Octopus\Command\BuildCommand;
use Symfony\Component\Console\Application;

class OctopusApplicagtion extends Application
{
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);

        $this->add(new BuildCommand());
    }
}
