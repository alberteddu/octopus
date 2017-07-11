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
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

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

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var bool
     */
    private $overwrite = false;

    /**
     * @var HelperSet
     */
    private $helperSet;

    public function __construct(
        Parser $parser,
        Validator $validator,
        Render $render,
        InputInterface $input,
        OutputInterface $output
    )
    {
        $this->parser = $parser;
        $this->validator = $validator;
        $this->render = $render;
        $this->input = $input;
        $this->output = $output;
        $this->helperSet = new HelperSet([new QuestionHelper()]);
    }

    public function build(Environment $environment)
    {
        $configuration = $environment->getConfiguration();
        $valid = $this->validator->validateConfiguration($configuration, $errors);

        if (!$valid) {
            foreach ($errors as $error) {
                $this->output->writeln($error);
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
        $overwrite = $this->overwrite;
        $willOverwrite = $this->overwrite;

        if (is_file($path) && !$this->overwrite) {
            /** @var QuestionHelper $question */
            $question = $this->helperSet->get('question');
            $overwrite = $question->ask($this->input, $this->output, new ChoiceQuestion(
                sprintf('File <info>%s</info> already exists. Overwrite?', realpath($path)),
                ['Skip', 'Overwrite', 'Overwrite all'],
                0
            ));

            if ($overwrite === 'Skip') {
                $this->output->writeln(sprintf('<comment>Skipped "%s"</comment>', realpath($path)));

                return;
            }

            $willOverwrite = true;
        }

        if (!is_dir($dirname)) {
            mkdir($dirname, 0777, true);
        }

        $template = $this->render->render($output, $this->environment, $contextBlueprint);

        if ($willOverwrite) {
            // Check if the file contains any dynamic section
            // that must be preserved.
            $currentMatches = [];
            $currentContents = file_get_contents($path);
            $pattern = '!\[octopus\:(\w+)\](.*)\[\/octopus\:\1\]!s';
            preg_match_all($pattern, $currentContents, $matches);
            $i = 0;
            foreach ($matches[1] as $name) {
                $currentMatches[$name] = $matches[2][$i];
                $i++;
            }
            $template = preg_replace_callback($pattern, function (array $matches) use ($currentMatches) {
                if (isset($currentMatches[$matches[1]])) {
                    $dynamic = $currentMatches[$matches[1]];
                } else {
                    $dynamic = '';
                }

                return sprintf('[octopus:%s]%s[/octopus:%s]', $matches[1], $dynamic, $matches[1]);
            }, $template);
        }

        file_put_contents($path, $template);

        if ($willOverwrite) {
            $this->output->writeln(sprintf('Overwrote file <comment>"%s"</comment>', realpath($path)));
        } else {
            $this->output->writeln(sprintf('Wrote file <info>"%s"</info>', realpath($path)));
        }

        if ($overwrite === 'Overwrite all') {
            $this->overwrite = true;
        }
    }

    public function buildDirectory(Output $output, BlueprintInstance $contextBlueprint = null)
    {
        $path = $this->parsePath($output->getPath(), $contextBlueprint);

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
            $this->output->writeln(sprintf('Wrote directory <info>"%s"</info>', realpath($path)));
        } else {
            $this->output->writeln(sprintf('Directory exists: <comment>"%s"</comment>', realpath($path)));
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
