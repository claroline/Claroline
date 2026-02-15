<?php

namespace Claroline\TransferBundle\Transfer\Adapter;

use Claroline\TransferBundle\Transfer\Adapter\Explain\Csv\Explanation;

interface AdapterInterface
{
    public function supports(string $mimeType): bool;

    /**
     * Build the list of object from the content submitted by a user and the data Schema.
     */
    public function decodeSchema(string $content, Explanation $explanation): array;

    /**
     * Explain how to build the content for the specified mime-type from the json-schema.
     */
    public function explainSchema(\stdClass $schema, string $mode): Explanation;

    /**
     * Explain how to build the schema when using an identifier from schema.
     *
     * @param \stdClass[] $schemas
     */
    public function explainIdentifiers(array $schemas): Explanation;

    /**
     * Format and dump exported data into a file.
     *
     * NB. The dumping process is delegated to the Adapter because of performance reasons.
     * For heavy exports, we need to be able to dump data regularly during the process to free memory.
     */
    public function dump(string $fileDest, array $data, array $schema, ?array $options = [], ?array $extra = [], ?bool $append = false): void;
}
