<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\DTO;

use Alberteddu\Octopus\Parser\Parser;

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

    /**
     * @var string
     */
    private $templatePath;

    public function __construct(Configuration $configuration, string $path)
    {
        $this->configuration = $configuration;
        $this->path = $path;
        $this->templatePath = joinPaths($path, $configuration->getTemplates());
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
        return $this->templatePath;
    }

    /**
     * @param string $templatePath
     *
     * @return Environment
     */
    public function setTemplatePath(string $templatePath): Environment
    {
        $this->templatePath = $templatePath;

        return $this;
}
}
