<?php

namespace Claroline\AppBundle\API\Transfer\Adapter;

use Claroline\AppBundle\API\Transfer\Adapter\Explain\Csv\Explanation;

interface AdapterInterface
{
    /**
     * Build the list of object from the content submitted by a user and the data Schema.
     *
     * @param mixed $content
     *
     * @return array
     */
    public function decodeSchema($content, Explanation $explanation);

    /**
     * return a list of supported mime-types for the data schema.
     *
     * @return array
     */
    public function getMimeTypes();

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
     * format the data for the export.
     *
     * @param array $data    - the serialized objects
     * @param array $options - a list of options
     *
     * @return array
     */
    public function format(array $data, array $options);
}
