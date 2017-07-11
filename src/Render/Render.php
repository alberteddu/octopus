<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\Render;

use Alberteddu\Octopus\DTO\BlueprintInstance;
use Alberteddu\Octopus\DTO\Environment;
use Alberteddu\Octopus\DTO\Output;
use Twig_Environment;
use Twig_Loader_Filesystem;

class Render
{
    public function render(Output $output, Environment $environment, BlueprintInstance $blueprintContext = null): string
    {
        if (!$output->getTemplate()) {
            return '';
        }

        $loader = new Twig_Loader_Filesystem([
            $environment->getTemplatePath()
        ]);
        $twig = new Twig_Environment($loader);

        return $twig->render($output->getTemplate(), [
            'coniguration' => $environment->getConfiguration(),
            'blueprint' => $blueprintContext,
        ]);
    }
}
