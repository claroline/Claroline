<?php

namespace UJM\ExoBundle\Tests\Controller\Api;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Persistence\ObjectManager;
use UJM\ExoBundle\Entity\Choice;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Hint;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Testing\Persister;
use UJM\ExoBundle\Testing\RequestTrait;

/**
 * Tests that are common to all exercise / question types.
 */
class ExerciseControllerCommonTest extends TransactionalTestCase
{
    use RequestTrait;

    /** @var ObjectManager */
    private $om;
    /** @var Persister */
    private $persist;
    /** @var User */
    private $john;
    /** @var User */
    private $bob;
    /** @var User */
    private $admin;
    /** @var Choice */
    private $ch1;
    /** @var Choice */
    private $ch2;
    /** @var Question */
    private $qu1;
    /** @var Hint */
    private $hi1;
    /** @var Exercise */
    private $ex1;

    protected function setUp()
    {
        parent::setUp();
        $this->om = $this->client->getContainer()->get('claroline.persistence.object_manager');
        $manager = $this->client->getContainer()->get('ujm.exo.paper_manager');
        $this->persist = new Persister($this->om, $manager);
        $this->john = $this->persist->user('john');
        $this->bob = $this->persist->user('bob');

        $this->persist->role('ROLE_ADMIN');
        $this->admin = $this->persist->user('admin');

        $this->ch1 = $this->persist->qcmChoice('ch1', 1, 1);
        $this->ch2 = $this->persist->qcmChoice('ch2', 2, 0);
        $this->qu1 = $this->persist->qcmQuestion('qu1', [$this->ch1, $this->ch2]);
        $this->hi1 = $this->persist->hint($this->qu1, 'hi1');
        $this->ex1 = $this->persist->exercise('ex1', [$this->qu1], $this->john);

        // Set up Exercise permissions
        // create 'open' mask in db
        $type = $this->ex1->getResourceNode()->getResourceType();
        $this->persist->maskDecoder($type, 'open', 1);
        $this->om->flush();

        $rightsManager = $this->client->getContainer()->get('claroline.manager.rights_manager');
        $roleManager = $this->client->getContainer()->get('claroline.manager.role_manager');

        // add open permissions to all users
        $rightsManager->editPerms(1, $roleManager->getRoleByName('ROLE_USER'), $this->ex1->getResourceNode());

        $this->om->flush();
    }

    public function testAnonymousExport()
    {
        $this->request('GET', "/exercise/api/exercises/{$this->ex1->getId()}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testNonCreatorExport()
    {
        $this->request('GET', "/exercise/api/exercises/{$this->ex1->getId()}", $this->bob);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testNonCreatorAdminExport()
    {
        $this->request('GET', "/exercise/api/exercises/{$this->ex1->getId()}", $this->admin);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testExport()
    {
        $this->request('GET', "/exercise/api/exercises/{$this->ex1->getId()}", $this->john);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals($this->ex1->getId(), $content->id);
        $this->assertEquals('ex1', $content->meta->title);
        $this->assertEquals('qu1', $content->steps[0]->items[0]->title);
    }

    /**
     * Minimal exercise export is used to get exercise data from paper list and paper details views
     * It returns only exercise metadata and id and is available for all "CAN OPEN RESOURCE" users.
     */
    public function testMinimalExport()
    {
        $this->request('GET', "/exercise/api/exercises/{$this->ex1->getId()}/minimal", $this->john);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals($this->ex1->getId(), $content->id);
        $this->assertEquals('ex1', $content->meta->title);
        $this->assertFalse(property_exists($content, 'steps'));
    }

    public function testAnonymousAttempt()
    {
        $this->request('POST', "/exercise/api/exercises/{$this->ex1->getId()}/attempts");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAttempt()
    {
        $this->request('POST', "/exercise/api/exercises/{$this->ex1->getId()}/attempts", $this->john);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertInternalType('object', $content);
    }

    /**
     * Checks that a basic user (ie not admin of the resource)
     * Can not make a new attempt if max attempts is reached.
     */
    public function testAttemptMaxAttemptsReached()
    {
        // set exercise max attempts
        $this->ex1->setMaxAttempts(1);
        // first attempt for bob
        $pa1 = $this->persist->paper($this->bob, $this->ex1);
        // finish bob's first paper
        $this->persist->finishpaper($pa1);
        $this->om->flush();

        // second attempt for bob
        $this->request('POST', "/exercise/api/exercises/{$this->ex1->getId()}/attempts", $this->bob);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals('Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException', $content->error->exception[0]->class);
        $this->assertEquals('max attempts reached', $content->error->exception[0]->message);
    }

    /**
     * Checks that an admin user (ie admin of the resource)
     * Can make a new attempt even if max attempts is reached.
     */
    public function testAttemptMaxAttemptsReachedAdmin()
    {
        // set exercise max attempts
        $this->ex1->setMaxAttempts(1);
        // first attempt for john
        $pa1 = $this->persist->paper($this->john, $this->ex1);
        // finish john's first paper
        $this->persist->finishpaper($pa1);
        $this->om->flush();

        // second attempt for john
        $this->request('POST', "/exercise/api/exercises/{$this->ex1->getId()}/attempts", $this->john);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertInternalType('object', $content);
    }

    /**
     * Checks the count of finished papers.
     */
    public function testCountFinishedPaper()
    {
        // create one paper that will be ended
        $pa1 = $this->persist->paper($this->john, $this->ex1);
        // create another paper that will not be ended
        $pa2 = $this->persist->paper($this->john, $this->ex1);
        // finish first john's paper
        $this->persist->finishpaper($pa1);
        $this->om->flush();

        // count john's finished papers
        $this->request('GET', "/exercise/api/exercises/{$this->ex1->getId()}/papers/count", $this->john);
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertTrue((int) $content === 1);
    }

    public function testAnonymousPapers()
    {
        $this->request('GET', "/exercise/api/exercises/{$this->ex1->getId()}/papers");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Checks that as a "normal" user I'll only see my own papers even if another user's paper exists.
     */
    public function testUserPapers()
    {
        // creator of the resource is considered as administrator of the resource
        $pa1 = $this->persist->paper($this->bob, $this->ex1);
        // check that only one paper will be returned even if another user paper exists
        $pa2 = $this->persist->paper($this->admin, $this->ex1);
        $this->om->flush();

        $this->request('GET', "/exercise/api/exercises/{$this->ex1->getId()}/papers", $this->bob);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(1, count($content));
        $this->assertEquals($pa1->getId(), $content[0]->id);
    }

    /**
     * Checks that as a "admin" user (ie creator of the exercise)
     * I'll see all exercise's papers.
     */
    public function testAdminPapers()
    {
        $pa1 = $this->persist->paper($this->admin, $this->ex1);
        $pa2 = $this->persist->paper($this->john, $this->ex1);
        $pa3 = $this->persist->paper($this->bob, $this->ex1);
        $pa4 = $this->persist->paper($this->bob, $this->ex1);
        $this->om->flush();

        $this->request('GET', "/exercise/api/exercises/{$this->ex1->getId()}/papers", $this->john);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(4, count($content));
        $this->assertEquals($pa1->getId(), $content[0]->id);
        $this->assertEquals($pa2->getId(), $content[1]->id);
        $this->assertEquals($pa3->getId(), $content[2]->id);
        $this->assertEquals($pa4->getId(), $content[3]->id);
    }
}
