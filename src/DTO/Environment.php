<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\DTO;

class Environment
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var string
     */
    private $path;

    public function __construct(Configuration $configuration, string $path)
    {
        $this->configuration = $configuration;
        $this->path = $path;
    }

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    public function getTemplatePath(): string
    {
        return joinPaths($this->getPath(), $this->configuration->getTemplates());
    }
}
