<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\Parser;

use Twig_Environment;

class Parser
{
    public function parse($subject, $substitutions)
    {
        $twig = new Twig_Environment(new \Twig_Loader_Array(['template' => $subject]));
        return $twig->render('template', $substitutions);
    }
}
