<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\DTO;

use JMS\Serializer\Annotation as Serializer;

class Configuration
{
    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $target = './';

    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $templates = './templates';

    /**
     * @var string[]
     *
     * @Serializer\Type("array<string, string>")
     */
    private $variables = [];

    /**
     * @var Blueprint[]
     *
     * @Serializer\Type("array<string, Alberteddu\Octopus\DTO\Blueprint>")
     */
    private $blueprints = [];

    /**
     * @var array[]
     *
     * @Serializer\Type("array<string, array>")
     */
    private $data = [];

    /**
     * @var Output[]
     *
     * @Serializer\Type("array<Alberteddu\Octopus\DTO\Output>")
     */
    private $output;

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @param string $target
     *
     * @return Configuration
     */
    public function setTarget(string $target): Configuration
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplates(): string
    {
        return $this->templates;
    }

    /**
     * @param string $templates
     *
     * @return Configuration
     */
    public function setTemplates(string $templates): Configuration
    {
        $this->templates = $templates;

        return $this;
    }

    /**
     * @return \string[]
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @param \string[] $variables
     *
     * @return Configuration
     */
    public function setVariables(array $variables): Configuration
    {
        $this->variables = $variables;

        return $this;
    }

    /**
     * @return Blueprint[]
     */
    public function getBlueprints(): array
    {
        return $this->blueprints;
    }

    public function blueprintExists(string $blueprintName): bool
    {
        return isset($this->blueprints[$blueprintName]);
    }

    public function getBlueprint(string $blueprintName): ?Blueprint
    {
        if (!$this->blueprintExists($blueprintName)) {
            return null;
        }

        return $this->blueprints[$blueprintName];
    }

    /**
     * @param Blueprint[] $blueprints
     *
     * @return Configuration
     */
    public function setBlueprints(array $blueprints): Configuration
    {
        $this->blueprints = $blueprints;

        return $this;
    }

    /**
     * @return \array[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param \array[] $data
     *
     * @return Configuration
     */
    public function setData(array $data): Configuration
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return Output[]
     */
    public function getOutput(): array
    {
        return $this->output;
    }

    /**
     * @param Output[] $output
     *
     * @return Configuration
     */
    public function setOutput(array $output): Configuration
    {
        $this->output = $output;

        return $this;
    }
}
