<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\DTO;

use JMS\Serializer\Annotation as Serializer;

class Output
{
    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $type;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $path;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $template;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $blueprint;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $group;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate(string $template)
    {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getBlueprint(): ?string
    {
        return $this->blueprint;
    }

    /**
     * @param string $blueprint
     */
    public function setBlueprint(string $blueprint)
    {
        $this->blueprint = $blueprint;
    }

    /**
     * @return string
     */
    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * @param string $group
     */
    public function setGroup(string $group)
    {
        $this->group = $group;
    }

    public function isFile(): bool
    {
        return 'file' === $this->getType();
    }

    public function isDirectory(): bool
    {
        return 'directory' === $this->getType();
    }

    public function cycles(): bool
    {
        return null !== $this->getBlueprint();
    }
}
