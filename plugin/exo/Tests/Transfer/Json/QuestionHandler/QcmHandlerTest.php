<?php

namespace UJM\ExoBundle\Transfer\Json\QuestionHandler;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;

class QcmHandlerTest extends TransactionalTestCase
{
    /**
     * @var QcmHandler
     */
    private $handler;

    /**
     * @var string
     */
    private $dataDir;

    protected function setUp()
    {
        parent::setUp();
        $this->handler = $this->client->getContainer()->get('ujm.exo.qcm_handler');
        $this->dataDir = realpath(__DIR__.'/../../../Data/json/question');
    }

    public function testPostValidateInconsistentSolutionId()
    {
        $data = json_decode(file_get_contents("{$this->dataDir}/invalid/qcm-inconsistent-solution-id.json"));
        $errors = $this->handler->validateAfterSchema($data);
        $expected = [
            'path' => 'solutions[1]',
            'message' => "id 3 doesn't match any choice id",
        ];
        $this->assertContains($expected, $errors);
    }

    /**
     * @dataProvider nonPositiveScoreProvider
     *
     * @param string $dataFileName
     */
    public function testPostValidateNonPositiveScore($dataFileName)
    {
        $data = json_decode(file_get_contents("{$this->dataDir}/invalid/{$dataFileName}.json"));
        $errors = $this->handler->validateAfterSchema($data);
        $expected = [
            'path' => 'solutions',
            'message' => 'there is no solution with a positive score',
        ];
        $this->assertContains($expected, $errors);
    }

    public function nonPositiveScoreProvider()
    {
        return [
            ['qcm-negative-score'],
            ['qcm-zero-score'],
        ];
    }
}
