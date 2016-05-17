<?php

namespace UJM\ExoBundle\Transfer\Json\QuestionHandler;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;

class MatchHandlerTest extends TransactionalTestCase
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
        $this->handler = $this->client->getContainer()->get('ujm.exo.match_handler');
        $this->dataDir = realpath(__DIR__.'/../../../Data/json/question');
    }

    public function testPostValidateInconsistentSolutionProposalId()
    {
        $data = json_decode(file_get_contents("{$this->dataDir}/invalid/match-inconsistent-solution-proposal-id.json"));
        $errors = $this->handler->validateAfterSchema($data);
        $expected = [
            'path' => 'solutions[0]',
            'message' => "id 1 doesn't match any proposal id",
        ];
        $this->assertContains($expected, $errors);
    }

    public function testPostValidateInconsistentSolutionLabelId()
    {
        $data = json_decode(file_get_contents("{$this->dataDir}/invalid/match-inconsistent-solution-label-id.json"));
        $errors = $this->handler->validateAfterSchema($data);
        $expected = [
            'path' => 'solutions[1]',
            'message' => "id 12 doesn't match any label id",
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
            ['match-negative-score'],
            ['match-zero-score'],
        ];
    }
}
