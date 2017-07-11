<?php

declare(strict_types=1);

namespace Alberteddu\Octopus;

use Alberteddu\Octopus\DTO\Configuration;
use Alberteddu\Octopus\Builder\Builder;
use Alberteddu\Octopus\DTO\Environment;
use Alberteddu\Octopus\Validator\Validator;
use JMS\Serializer\SerializerInterface;
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
        $container->setParameter('root_path', __DIR__ . '/..');
        $loader = new YamlFileLoader($container, new FileLocator(self::CONFIG));
        $loader->load('container.yml');

        $container->compile();

        $this->container = $container;
    }

    public function buildFromFile(string $path)
    {
        /** @var Builder $builder */
        $builder = $this->container->get('builder');
        /** @var SerializerInterface $serializer */
        $serializer = $this->container->get('serializer');
        /** @var Validator $validator */
        $validator = $this->container->get('validator');

        $pwd = dirname($path);

        $contents = file_get_contents($path);
        $valid = $validator->validateConfigurationString($contents, $errors);

        if (!$valid) {
            foreach ($errors as $error) {
                echo $error['message'] . PHP_EOL;
            }

            return;
        }

        /** @var Configuration $configuration */
        $configuration = $serializer->deserialize($contents, Configuration::class, 'json');
        $environment = new Environment($configuration, $pwd);

        $builder->build($environment);
    }
}
