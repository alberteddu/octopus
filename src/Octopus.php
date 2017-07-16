<?php

declare(strict_types=1);

namespace Alberteddu\Octopus;

use Alberteddu\Octopus\DTO\Configuration;
use Alberteddu\Octopus\Builder\Builder;
use Alberteddu\Octopus\DTO\Environment;
use Alberteddu\Octopus\Validator\Validator;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $container = new ContainerBuilder();
        $container->setParameter('root_path', __DIR__ . '/..');
        $container->set('input', $input);
        $container->set('output', $output);
        $loader = new YamlFileLoader($container, new FileLocator(self::CONFIG));
        $loader->load('container.yml');

        $container->compile();

        $this->container = $container;
    }

    public function getConfigurationFromFile(string $path): Configuration
    {
        /** @var SerializerInterface $serializer */
        $serializer = $this->container->get('serializer');
        /** @var Validator $validator */
        $validator = $this->container->get('validator');
        /** @var OutputInterface $output */
        $output = $this->container->get('output');

        $contents = file_get_contents($path);
        $valid = $validator->validateConfigurationString($contents, $errors);

        if (!$valid) {
            foreach ($errors as $error) {
                $output->writeln(sprintf('<error>%s: %s</error>', $error['property'], $error['message']));
            }

            return null;
        }

        /** @var Configuration $configuration */
        $configuration = $serializer->deserialize($contents, Configuration::class, 'json');

        return $configuration;
    }

    public function buildFromFile(string $path)
    {
        /** @var Builder $builder */
        $builder = $this->container->get('builder');

        $currentWorkingDirectory = dirname($path);
        $configuration = $this->getConfigurationFromFile($path);
        $environment = new Environment($configuration, $currentWorkingDirectory);

        $builder->build($environment);
    }

    public function build(Environment $environment)
    {
        /** @var Builder $builder */
        $builder = $this->container->get('builder');

        $builder->build($environment);
    }
}
