<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\Service;

use JMS\Serializer\SerializerBuilder as BaseSerializerBuilder;
use JMS\Serializer\SerializerInterface;

class SerializerBuilder extends BaseSerializerBuilder
{
    public static function createOctopusSerializer(): SerializerInterface
    {
        return self::create()
                   ->build();
    }
}
