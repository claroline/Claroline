<?php

namespace Claroline\TransferBundle\Transfer\Adapter\Explain\Csv;

use Claroline\TransferBundle\Transfer\Importer\AbstractImporter;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExplanationBuilder
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly string $mode = 'default'
    ) {
    }

    private function explainObject(\stdClass $data, Explanation $explanation, string $currentPath, ?bool $isArray = false): void
    {
        if (!isset($data->properties)) {
            return;
        }

        foreach ($data->properties as $name => $property) {
            $whereAmI = '' === $currentPath ? $name : $currentPath.'.'.$name;

            if (!isset($property->type)) {
                return;
            }

            if ('array' === $property->type || (is_array($property->type) && in_array('array', $property->type))) {
                $this->explainSchema($property->items, $explanation, $whereAmI, true);
            } elseif ('object' === $property->type || (is_array($property->type) && in_array('object', $property->type))) {
                $this->explainObject($property, $explanation, $whereAmI, $isArray);
            }

            if (!in_array($property->type, ['array', 'object'])) {
                if (AbstractImporter::MODE_CREATE === $this->mode) {
                    $required = isset($data->claroline) && isset($data->claroline->requiredAtCreation) ? in_array($name, $data->claroline->requiredAtCreation) : false;
                } else {
                    $required = isset($data->required) ? in_array($name, $data->required) : false;
                }
                $explanation->addProperty(
                    $whereAmI,
                    $property->type,
                    $this->translator->trans($this->getDescription($property), [], 'schema'),
                    $required,
                    $isArray
                );
            }
        }
    }

    /**
     * A oneOf is simply another schema that needs to be explained.
     */
    private function explainOneOf(\stdClass $data, Explanation $explanation, string $currentPath, ?bool $isArray = false): void
    {
        $explanation->addOneOf(array_map(function ($oneOf) use ($currentPath, $isArray) {
            return $this->explainSchema($oneOf, null, $currentPath, $isArray);
        }, $data->oneOf), 'an auto generated descr');
    }

    /**
     * Explain how to import, according to the json-schema for a given mime type (csv)
     * Here, we'll give a csv description according to the schema
     * This is only a first version because not everything is supported by csv.
     */
    public function explainSchema(
        \stdClass $data,
        ?Explanation $explanation = null,
        ?string $currentPath = '',
        ?bool $isArray = false
    ): Explanation {
        if (!$explanation) {
            $explanation = new Explanation();
        }
        // parse the JSON and explain what to do

        if (isset($data->type)) {
            $this->explainObject($data, $explanation, $currentPath, $isArray);
        } elseif (property_exists($data, 'oneOf')) {
            $this->explainOneOf($data, $explanation, $currentPath, $isArray);
        } elseif (property_exists($data, 'allOf')) {
        } elseif (property_exists($data, 'anyOf')) {
        }

        return $explanation;
    }

    public function explainIdentifiers(array $schemas): Explanation
    {
        $explanation = new Explanation();

        foreach ($schemas as $prop => $schema) {
            if (isset($schema->claroline)) {
                $identifiers = $schema->claroline->ids;
            } else {
                $identifiers = [];
            }

            $explanation->setIdentifiers($identifiers);

            if (isset($schema->type) && 'object' === $schema->type) {
                $oneOfs = [];
                foreach ($identifiers as $property) {
                    $data = $schema->properties->{$property};
                    $oneOfs[] = new Explanation([new Property(
                        $prop.'.'.$property,
                        $data->type,
                        $this->translator->trans($this->getDescription($data), [], 'schema'),
                        false,
                        false
                    )]);
                }

                $explanation->addOneOf(
                    $oneOfs,
                    $this->translator->trans('One of the following list of properties', [], 'schema')
                );
            }
        }

        return $explanation;
    }

    private function getProperty(\stdClass $data, string $prop, mixed $default): mixed
    {
        if (isset($data->{$prop})) {
            return $data->{$prop};
        }

        return $default;
    }

    private function getDescription(\stdClass $property): string
    {
        return $this->getProperty($property, 'description', '');
    }
}
