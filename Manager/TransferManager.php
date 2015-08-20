<?php

namespace HeVinci\CompetencyBundle\Manager;

use JsonSchema\RefResolver;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;

class TransferManager
{
    public function validate($framework)
    {
        $validator = new Validator();
        $validator->check($framework, $this->getSchema());

        return $validator->getErrors();
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