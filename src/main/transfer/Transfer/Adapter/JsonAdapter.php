<?php

namespace Claroline\TransferBundle\Transfer\Adapter;

use Claroline\TransferBundle\Transfer\Adapter\Explain\Csv\Explanation;
use Symfony\Component\Filesystem\Filesystem;

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
        // this is wrong
        return new Explanation();
    }

    public function explainIdentifiers(array $schema)
    {
        // this is wrong
        return new Explanation();
    }

    public function dump(string $fileDest, array $data, array $schema, ?array $options = [], ?array $extra = [], ?bool $append = false): void
    {
        $fs = new FileSystem();

        $fs->appendToFile($fileDest, json_encode($data));
    }
}
