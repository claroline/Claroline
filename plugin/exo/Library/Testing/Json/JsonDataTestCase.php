<?php

namespace UJM\ExoBundle\Library\Testing\Json;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;

/**
 * JsonDataTestCase is the base class for tests needing JSON data.
 * It allows to load JSON data from files.
 */
abstract class JsonDataTestCase extends TransactionalTestCase
{
    /**
     * @var string
     */
    private $formatDir;

    /**
     * @var string
     */
    private $dataDir;

    protected function setUp()
    {
        parent::setUp();

        $vendorDir = realpath("{$this->client->getKernel()->getRootDir()}/../vendor");
        $this->formatDir = "{$vendorDir}/json-quiz/json-quiz/format";
        $this->dataDir = "{$vendorDir}/claroline/distribution/plugin/exo/Tests/Data/json";
    }

    /**
     * Loads data set from examples of the JSON quiz schema.
     *
     * @param string $uri
     *
     * @return mixed
     */
    protected function loadExampleData($uri)
    {
        $json = file_get_contents("{$this->formatDir}/$uri");

        return json_decode($json);
    }

    /**
     * Loads data set from local Tests directory.
     *
     * @param string $uri
     *
     * @return mixed
     */
    public function loadTestData($uri)
    {
        $json = file_get_contents("{$this->dataDir}/$uri");

        return json_decode($json);
    }
}
