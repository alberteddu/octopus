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
     * @var mixed
     *
     * @Serializer\Type("default")
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
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     *
     * @return BlueprintVariable
     */
    public function setDefault($default): BlueprintVariable
    {
        $this->default = $default;

        return $this;
    }

    public function checkType($value): bool
    {
        switch ($this->type) {
            case 'string':
                return is_string($value);
                break;
            case 'integer';
                return is_integer($value);
                break;
            case 'boolean':
                return is_bool($value);
                break;
            case 'array':
                return is_array($value);
                break;
            case 'object':
                return is_array($value);
                break;
            default:
                return false;
        }
    }
}
