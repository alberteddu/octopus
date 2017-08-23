<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\Serializer;

use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder as BaseSerializerBuilder;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\VisitorInterface;

class SerializerBuilder extends BaseSerializerBuilder
{
    public static function createOctopusSerializer(): SerializerInterface
    {
        return self::create()
                   ->configureHandlers(function (HandlerRegistry $registry) {
                       $registry->registerHandler('deserialization', 'default', 'json',
                           function (VisitorInterface $visitor, $data, $type, $context) {
                               return $data;
                           }
                       );

                       $registry->registerHandler('serialization', 'default', 'json',
                           function (VisitorInterface $visitor, $data, $type, $context) {
                               return $data;
                           }
                       );
                   })
                   ->build();
    }
}
