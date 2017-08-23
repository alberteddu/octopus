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
use JMS\Serializer\SerializerInterface;
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
     * @var SerializerInterface
     */
    private $serializer;

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
        OutputInterface $output,
        SerializerInterface $serializer
    )
    {
        $this->parser = $parser;
        $this->validator = $validator;
        $this->render = $render;
        $this->input = $input;
        $this->output = $output;
        $this->serializer = $serializer;
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
            if ($output->hasGroup()) {
                $groups = [];

                foreach ($this->configuration->getData()[$output->getBlueprint()] as $blueprint) {
                    $value = $blueprint[$output->getGroup()];

                    if (!isset($groups[$value])) {
                        $groups[$value] = [];
                    }

                    $groups[$value][] = new BlueprintInstance($this->configuration->getBlueprint($output->getBlueprint()), $blueprint);
                }

                foreach ($groups as $group) {
                    if ($output->isDirectory()) {
                        $this->buildDirectory($output, null, $group);
                    }

                    if ($output->isFile()) {
                        $this->buildFile($output, null, $group);
                    }
                }

            } else {
                foreach ($this->configuration->getData()[$output->getBlueprint()] as $blueprint) {
                    $blueprintInstance = new BlueprintInstance($this->configuration->getBlueprint($output->getBlueprint()), $blueprint);

                    if ($output->isDirectory()) {
                        $this->buildDirectory($output, $blueprintInstance);
                    }

                    if ($output->isFile()) {
                        $this->buildFile($output, $blueprintInstance);
                    }
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

    public function buildFile(
        Output $output,
        BlueprintInstance $contextBlueprint = null,
        array $contextBlueprints = null
    )
    {
        $path = $this->parsePath($output->getPath(), $contextBlueprint, $contextBlueprints);
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

        if ($output->getTemplate()) {
            $template = $this->render->render(
                $this->parseString($output->getTemplate(), $contextBlueprint, $contextBlueprints),
                $this->environment,
                $contextBlueprint,
                $contextBlueprints
            );
        } else {
            $template = '';
        }

        if ($willOverwrite && is_file($path) && is_readable($path)) {
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

    public function buildDirectory(
        Output $output,
        BlueprintInstance $contextBlueprint = null,
        array $contextBlueprints = null
    )
    {
        $path = $this->parsePath($output->getPath(), $contextBlueprint, $contextBlueprints);

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
            $this->output->writeln(sprintf('Wrote directory <info>"%s"</info>', realpath($path)));
        } else {
            $this->output->writeln(sprintf('Directory exists: <comment>"%s"</comment>', realpath($path)));
        }
    }

    private function parseString(
        string $string,
        BlueprintInstance $contextBlueprint = null,
        array $contextBlueprints = null
    ): string
    {
        return $this->parser->parse($string, [
            'blueprints' => $contextBlueprints,
            'blueprint' => $contextBlueprint,
            'configuration' => $this->serializer->toArray($this->configuration),
        ]);
    }

    private function parsePath(
        string $path,
        BlueprintInstance $contextBlueprint = null,
        array $contextBlueprints = null
    ): string
    {
        return joinPaths($this->configuration->getTarget(), $this->parseString($path, $contextBlueprint, $contextBlueprints));
    }
}
