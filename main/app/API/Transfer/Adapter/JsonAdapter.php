<?php

namespace Claroline\AppBundle\API\Transfer\Adapter;

use Claroline\AppBundle\API\Transfer\Adapter\Explain\Csv\Explanation;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 * @DI\Tag("claroline.transfer.adapter")
 */
class JsonAdapter implements AdapterInterface
{
    public function decodeSchema($content, Explanation $schema)
    {
        return json_decode($content, true);
    }

    public function getMimeTypes()
    {
        return ['application/json', 'json'];
    }

    public function explainSchema(\stdClass $schema)
    {
        return $schema;
    }

    public function explainIdentifiers(array $schema)
    {
        return $schema;
    }

    public function decodeIdentifiers($data, array $schemas)
    {
        return json_decode($data);
    }

    public function format(array $data, array $options)
    {
        return $data;
    }
}
