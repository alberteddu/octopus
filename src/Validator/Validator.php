<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\Validator;

use Alberteddu\Octopus\DTO\BlueprintVariable;
use Alberteddu\Octopus\DTO\Configuration;
use JsonSchema\Validator as SchemaValidator;
use Symfony\Component\Console\Output\OutputInterface;

class Validator
{
    /**
     * @var SchemaValidator
     */
    private $schemaValidator;

    public function __construct(SchemaValidator $schemaValidator)
    {
        $this->schemaValidator = $schemaValidator;
    }

    public function validateConfigurationString(string $configuration, &$errors = []): bool
    {
        $configuration = json_decode($configuration);

        $this->schemaValidator->validate(
            $configuration,
            json_decode(file_get_contents(__DIR__ . '/../../config/schema.json'))
        );

        $errors = $this->schemaValidator->getErrors();

        return count($errors) === 0;
    }

    public function validateConfiguration(Configuration $configuration, &$errors = []): bool
    {
        $currentErrors = [];

        foreach ($configuration->getData() as $blueprintName => $capturesList) {
            // Check that data contains existing blueprints
            if (!$configuration->blueprintExists($blueprintName)) {
                $currentErrors[] = sprintf('<error>Blueprint %s does not exist.</error>', $blueprintName);

                continue;
            }

            $blueprint = $configuration->getBlueprint($blueprintName);

            foreach ($capturesList as $captures) {
                /**
                 * @var string            $variableName
                 * @var BlueprintVariable $variable
                 */
                foreach ($blueprint->getVariables() as $variableName => $variable) {
                    // Check that every requied blueprint variable is defined
                    if ($variable->isRequired() && !isset($captures[$variableName])) {
                        $currentErrors[] = sprintf('<error>Variable %s is required but not provided.</error>',
                            $variableName);

                        continue;
                    }

                    if (isset($captures[$variableName])) {
                        $actualVariable = $captures[$variableName];

                        // Check that the variable type is correct
                        if (!$variable->checkType($actualVariable)) {
                            $currentErrors[] = sprintf('<error>Variable %s should be of type %s</error>', $variableName,
                                $variable->getType());

                            continue;
                        }
                    }
                }

                foreach ($captures as $captureName => $captureValue) {
                    if (!$blueprint->variableExists($captureName)) {
                        $currentErrors[] = sprintf('<error>Variable %s is not allowed</error>', $captureName);
                    }
                }
            }
        }

        $errors = $currentErrors;

        return empty($currentErrors);
    }

}
