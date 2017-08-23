<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\Render;

use Alberteddu\Octopus\DTO\BlueprintInstance;
use Alberteddu\Octopus\DTO\Environment;
use Alberteddu\Octopus\DTO\Output;
use JMS\Serializer\SerializerInterface;
use Twig_Environment;
use Twig_Extension_Debug;
use Twig_Loader_Filesystem;

class Render
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function render(
        string $template,
        Environment $environment,
        BlueprintInstance $contextBlueprint = null,
        array $contextBlueprints = null
    ): string {
        if (substr($template, -4) === '.php') {
            return $this->renderPhp($template, $environment, $contextBlueprint, $contextBlueprints);
        } else {
            return $this->renderTwig($template, $environment, $contextBlueprint, $contextBlueprints);
        }
    }

    private function renderPhp(
        string $template,
        Environment $environment,
        BlueprintInstance $contextBlueprint = null,
        array $contextBlueprints = null
    ): string {
        $path = joinPaths($environment->getTemplatePath(), $template);
        $configuration = $environment->getConfiguration();
        $data = [];

        foreach ($configuration->getData() as $blueprintName => $dataList) {
            $blueprint = $configuration->getBlueprint($blueprintName);
            if (!isset($data[$blueprintName])) {
                $data[$blueprintName] = [];
            }

            foreach ($dataList as $datum) {
                $data[$blueprintName][] = new BlueprintInstance($blueprint, $datum);
            }
        }

        $blueprint = $contextBlueprint;
        $blueprints = $contextBlueprints;

        ob_start();
        include $path;
        return ob_get_clean();
    }

    private function renderTwig(
        string $template,
        Environment $environment,
        BlueprintInstance $contextBlueprint = null,
        array $contextBlueprints = null
    ): string {
        $configuration = $environment->getConfiguration();
        $loader = new Twig_Loader_Filesystem([
            $environment->getTemplatePath()
        ]);
        $twig = new Twig_Environment($loader, [
            'debug' => true
        ]);
        $twig->addExtension(new Twig_Extension_Debug());
        $data = [];

        foreach ($configuration->getData() as $blueprintName => $dataList) {
            $blueprint = $configuration->getBlueprint($blueprintName);
            if (!isset($data[$blueprintName])) {
                $data[$blueprintName] = [];
            }

            foreach ($dataList as $datum) {
                $data[$blueprintName][] = new BlueprintInstance($blueprint, $datum);
            }
        }

        return $twig->render($template, [
            'configuration' => $this->serializer->toArray($configuration),
            'blueprint' => $contextBlueprint,
            'blueprints' => $contextBlueprints,
            'data' => $data,
        ]);
    }
}
