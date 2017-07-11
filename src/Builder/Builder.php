<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\Builder;

use Alberteddu\Octopus\DTO\BlueprintInstance;
use Alberteddu\Octopus\DTO\Configuration;
use Alberteddu\Octopus\DTO\Environment;
use Alberteddu\Octopus\DTO\Output;
use Alberteddu\Octopus\Parser\Parser;
use Alberteddu\Octopus\Render\Render;
use Alberteddu\Octopus\Validator\Validator;

class Builder
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var Render
     */
    private $render;

    public function __construct(Parser $parser, Validator $validator, Render $render)
    {
        $this->parser = $parser;
        $this->validator = $validator;
        $this->render = $render;
    }

    public function build(Environment $environment)
    {
        $configuration = $environment->getConfiguration();
        $valid = $this->validator->validateConfiguration($configuration, $errors);

        if (!$valid) {
            foreach ($errors as $error) {
                echo $error . PHP_EOL;
            }

            return;
        }

        $this->environment = $environment;
        $this->configuration = $configuration;

        foreach ($configuration->getOutput() as $output) {
            $this->buildNode($output);
        }
    }

    public function buildNode(Output $output)
    {
        if ($output->cycles()) {
            foreach ($this->configuration->getData()[$output->getBlueprint()] as $blueprint) {
                $blueprintInstance = new BlueprintInstance($this->configuration->getBlueprint($output->getBlueprint()), $blueprint);

                if ($output->isDirectory()) {
                    $this->buildDirectory($output, $blueprintInstance);
                }

                if ($output->isFile()) {
                    $this->buildFile($output, $blueprintInstance);
                }
            }
        } else {
            if ($output->isDirectory()) {
                $this->buildDirectory($output);
            }

            if ($output->isFile()) {
                $this->buildFile($output);
            }
        }
    }

    public function buildFile(Output $output, BlueprintInstance $contextBlueprint = null)
    {
        $path = $this->parsePath($output->getPath(), $contextBlueprint);
        $dirname = dirname($path);

        if (!is_dir($dirname)) {
            mkdir($dirname, 0777, true);
        }

        file_put_contents($path, $this->render->render($output, $this->environment, $contextBlueprint));
    }

    public function buildDirectory(Output $output, BlueprintInstance $contextBlueprint = null)
    {
        $path = $this->parsePath($output->getPath(), $contextBlueprint);

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }

    private function parsePath(string $path, BlueprintInstance $contextBlueprint = null): string
    {
        return joinPaths($this->configuration->getTarget(), $this->parser->parse($path, [
            'blueprint' => $contextBlueprint,
            'variables' => (object) $this->configuration->getVariables(),
        ]));
    }
}
