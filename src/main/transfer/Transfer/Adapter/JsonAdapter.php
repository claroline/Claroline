<?php

namespace Claroline\TransferBundle\Transfer\Adapter;

use Claroline\TransferBundle\Transfer\Adapter\Explain\Csv\Explanation;

class JsonAdapter implements AdapterInterface
{
    public function decodeSchema($content, Explanation $schema)
    {
        return json_decode($content, true);
    }

    public function supports(string $mimeType): bool
    {
        return in_array($mimeType, ['application/json', 'json']);
    }

    public function explainSchema(\stdClass $schema, $mode)
    {
        return $schema;
    }

    public function explainIdentifiers(array $schema)
    {
        return $schema;
    }

    public function format(array $data, array $options)
    {
        return $data;
    }
}