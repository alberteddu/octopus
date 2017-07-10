<?php

declare(strict_types=1);

namespace Alberteddu\Octopus;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Octopus
{
    const CONFIG = __DIR__ . '/../config/';

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct()
    {
        $container = new ContainerBuilder();
        $loader = new YamlFileLoader($container, new FileLocator(self::CONFIG));
        $loader->load('container.yml');

        $container->compile();

        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
