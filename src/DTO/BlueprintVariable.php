<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\DTO;

use JMS\Serializer\Annotation as Serializer;

class BlueprintVariable
{
    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $type;

    /**
     * @var bool
     *
     * @Serializer\Type("boolean")
     */
    private $required = false;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $default;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return BlueprintVariable
     */
    public function setType(string $type): BlueprintVariable
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     *
     * @return BlueprintVariable
     */
    public function setRequired(bool $required): BlueprintVariable
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefault(): string
    {
        return $this->default;
    }

    /**
     * @param string $default
     *
     * @return BlueprintVariable
     */
    public function setDefault(string $default): BlueprintVariable
    {
        $this->default = $default;

        return $this;
    }
}
