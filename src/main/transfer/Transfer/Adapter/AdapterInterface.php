<?php

namespace Claroline\TransferBundle\Transfer\Adapter;

use Claroline\TransferBundle\Transfer\Adapter\Explain\Csv\Explanation;

interface AdapterInterface
{
    public function supports(string $mimeType): bool;

    /**
     * Build the list of object from the content submitted by a user and the data Schema.
     *
     * @param mixed $content
     *
     * @return array
     */
    public function decodeSchema($content, Explanation $explanation);

    /**
     * Explain how to build the content for the specified mime-type from the json-schema.
     *
     * @param string $mode
     *
     * @return Explanation
     */
    public function explainSchema(\stdClass $json, $mode);

    /**
     * Explain how to build the schema when using an identifier from schema.
     *
     * @param \stdClass[] $schemas
     *
     * @return Explanation
     */
    public function explainIdentifiers(array $schemas);

    /**
     * Format and dump exported data into a file.
     *
     * NB. the dumping process is delegated to the Adapter because of performances reasons.
     * For heavy exports, we need to be able to dump data regularly during the process in order to free memory.
     */
    public function dump(string $fileDest, array $data, ?array $options = [], ?array $extra = [], ?bool $append = false): void;
}
