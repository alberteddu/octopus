<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\Parser;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class Parser
{
    public function parse($subject, $substitutions)
    {
        $language = new ExpressionLanguage();
        $regex = '!\$\{([^\}]+)\}!';

        return preg_replace_callback($regex, function($matches) use ($substitutions, $language) {
            return $language->evaluate($matches[1], $substitutions);
        }, $subject);
    }
}
