<?php

namespace HeVinci\CompetencyBundle\Manager;

use HeVinci\CompetencyBundle\Transfer\JsonValidator;


class TransferManager
{
    private $jsonValidator;

    public function __construct(JsonValidator $jsonValidator)
    {
        $this->jsonValidator = $jsonValidator;
    }

    public function validate($framework)
    {
        // json schema (structure only)
        $this->jsonValidator->check($framework, $this->getSchema());
        $errors = array_map(function ($error) {
            return "{$error['message']} (path: {$error['property']})";
        }, $this->jsonValidator->getErrors());


        // internal data constraints
        $levels = $framework->scale->levels;


        return $errors;
    }

    private function getSchema()
    {
        static $schema;

        if (!$schema) {
            $schemaDir = realpath(__DIR__ . '/../Resources/format');
            $retriever = new UriRetriever();
            $schema = $retriever->retrieve("file://{$schemaDir}/framework.json");
            $refResolver = new RefResolver($retriever);
            $refResolver->resolve($schema);
        }

        return $schema;
    }
} 