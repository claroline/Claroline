<?php

namespace UJM\ExoBundle\Tests\Controller\Tool;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Testing\RequestTrait;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use UJM\ExoBundle\Library\Testing\Persister;

class QuestionBankControllerTest extends TransactionalTestCase
{
    use RequestTrait;

    /** @var ObjectManager */
    private $om;
    /** @var Persister */
    private $persist;

    public function setUp()
    {
        parent::setUp();

        $this->om = $this->client->getContainer()->get('claroline.persistence.object_manager');
        $this->persist = new Persister($this->om);
    }

    /**
     * The bank open action MUST renders the HTML view without errors.
     */
    public function testOpenRendersView()
    {
        // Try to open the bank with a "normal" user
        $user = $this->persist->user('bob');
        $this->om->flush();

        $crawler = $this->request('GET', '/questions', $user);

        // The user must have access to the exercise
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html')->count() > 0);
    }
}
