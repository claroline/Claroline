<?php

namespace UJM\ExoBundle\Tests\Controller\Api;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\RequestTrait;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Attempt\PaperGenerator;
use UJM\ExoBundle\Library\Testing\Persister;
use UJM\ExoBundle\Manager\Attempt\PaperManager;

/**
 * Tests the papers endpoints (list, read, delete, ...).
 *
 * @todo : do not use PaperGenerator. This is not needed to have functional papers here
 */
class PaperControllerTest extends TransactionalTestCase
{
    use RequestTrait;

    /** @var ObjectManager */
    private $om;
    /** @var PaperGenerator */
    private $paperGenerator;
    /** @var Persister */
    private $persist;
    /** @var PaperManager */
    private $paperManager;
    /** @var RightsManager */
    private $rightsManager;
    /** @var RoleManager */
    private $roleManager;
    /** @var User */
    private $john;
    /** @var User */
    private $bob;
    /** @var Exercise */
    private $exercise;

    protected function setUp()
    {
        parent::setUp();

        $this->om = $this->client->getContainer()->get('claroline.persistence.object_manager');
        $this->paperGenerator = $this->client->getContainer()->get('ujm_exo.generator.paper');
        $this->paperManager = $this->client->getContainer()->get('ujm_exo.manager.paper');
        $this->rightsManager = $this->client->getContainer()->get('claroline.manager.rights_manager');
        $this->roleManager = $this->client->getContainer()->get('claroline.manager.role_manager');

        $this->persist = new Persister($this->om);
        $this->john = $this->persist->user('john');
        $this->bob = $this->persist->user('bob');

        $this->exercise = $this->persist->exercise('ex1', [], $this->john);

        // Set up Exercise permissions
        // create 'open' mask in db
        $type = $this->exercise->getResourceNode()->getResourceType();
        $this->persist->maskDecoder($type, 'open', 1);
        $this->om->flush();

        // add open permissions to all users
        $this->rightsManager->editPerms(1, $this->roleManager->getRoleByName('ROLE_ANONYMOUS'), $this->exercise->getResourceNode());

        $this->om->flush();
    }

    /**
     * A user who does not have access to the exercise MUST not have access to the papers list.
     */
    public function testUserCannotOpenExerciseCannotOpenPapers()
    {
        $paper = $this->paperGenerator->create($this->exercise, $this->bob);
        $this->om->persist($paper);

        // Removes permission
        $this->rightsManager->editPerms(0, $this->roleManager->getRoleByName('ROLE_ANONYMOUS'), $this->exercise->getResourceNode());

        $this->om->flush();

        // Test the list route
        $this->request('GET', "/api/exercises/{$this->exercise->getUuid()}/papers", $this->bob);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * A user who does not have access to the exercise MUST not have access to the paper detail.
     */
    public function testUserCannotOpenExerciseCannotOpenPaperDetail()
    {
        $paper = $this->paperGenerator->create($this->exercise, $this->bob);
        $this->om->persist($paper);

        // Removes permission
        $this->rightsManager->editPerms(0, $this->roleManager->getRoleByName('ROLE_ANONYMOUS'), $this->exercise->getResourceNode());

        $this->om->flush();

        // Test the detail of one paper route
        $this->request('GET', "/api/exercises/{$this->exercise->getUuid()}/papers/{$paper->getUuid()}", $this->bob);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * An anonymous user MUST NOT have access to papers.
     */
    public function testAnonymousPapers()
    {
        $this->request('GET', "/api/exercises/{$this->exercise->getUuid()}/papers");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * A "normal" user MUST have access only to its own papers and MUST NOT see papers of other users.
     */
    public function testUserPapers()
    {
        // creator of the resource is considered as administrator of the resource
        $pa1 = $this->paperGenerator->create($this->exercise, $this->bob);

        // check that only one paper will be returned even if another user paper exists
        $pa2 = $this->paperGenerator->create($this->exercise, $this->john);

        $this->om->persist($pa1);
        $this->om->persist($pa2);
        $this->om->flush();

        $this->request('GET', "/api/exercises/{$this->exercise->getUuid()}/papers", $this->bob);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(1, count($content));

        $this->assertEquals($pa1->getUuid(), $content[0]->id);
    }

    /**
     * An "admin" user MUST have access to the papers of all the users who have passed the test.
     */
    public function testAdminPapers()
    {
        $pa1 = $this->paperGenerator->create($this->exercise, $this->john);
        $pa2 = $this->paperGenerator->create($this->exercise, $this->john);
        $pa3 = $this->paperGenerator->create($this->exercise, $this->bob);
        $pa4 = $this->paperGenerator->create($this->exercise, $this->bob);

        $this->om->persist($pa1);
        $this->om->persist($pa2);
        $this->om->persist($pa3);
        $this->om->persist($pa4);
        $this->om->flush();

        $this->request('GET', "/api/exercises/{$this->exercise->getUuid()}/papers", $this->john);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(4, count($content));
        $this->assertEquals($pa1->getUuid(), $content[0]->id);
        $this->assertEquals($pa2->getUuid(), $content[1]->id);
        $this->assertEquals($pa3->getUuid(), $content[2]->id);
        $this->assertEquals($pa4->getUuid(), $content[3]->id);
    }

    /**
     * An "anonymous" user MUST have access to the detail of its own papers.
     */
    public function testAnonymousPaper()
    {
        // Create a paper for the anonymous
        $paper = $this->paperGenerator->create($this->exercise);
        $this->om->persist($paper);
        $this->om->flush();

        // Request the created paper
        $this->request('GET', "/api/exercises/{$this->exercise->getUuid()}/papers/{$paper->getUuid()}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * A "normal" user MUST have access to the detail of its own papers.
     */
    public function testUserPaper()
    {
        // Create a paper for user Bob (normal user)
        $paper = $this->paperGenerator->create($this->exercise, $this->bob);
        $this->om->persist($paper);
        $this->om->flush();

        // Request the created paper
        $this->request('GET', "/api/exercises/{$this->exercise->getUuid()}/papers/{$paper->getUuid()}", $this->bob);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // Validate the received content
        $this->assertIsValidPaperDetail($paper, json_decode($this->client->getResponse()->getContent()));
    }

    /**
     * A "normal" user MUST NOT have access to paper detail of other users.
     */
    public function testNonUserPaper()
    {
        // Let me introduce you : James, the normal user who will try to access to bob's paper
        $james = $this->persist->user('james');

        // Create a paper for user Bob
        $paper = $this->paperGenerator->create($this->exercise, $this->bob);
        $this->om->persist($paper);
        $this->om->flush();

        // Request the created paper
        $this->request('GET', "/api/exercises/{$this->exercise->getUuid()}/papers/{$paper->getUuid()}", $james);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * An "admin" user MUST have access to the detail of all user papers.
     */
    public function testAdminPaper()
    {
        // Create a paper for user Bob
        $paper = $this->paperGenerator->create($this->exercise, $this->bob);
        $this->om->persist($paper);
        $this->om->flush();

        // Let the admin John request the created paper
        $this->request('GET', "/api/exercises/{$this->exercise->getUuid()}/papers/{$paper->getUuid()}", $this->john);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // Validate the received content
        $this->assertIsValidPaperDetail($paper, json_decode($this->client->getResponse()->getContent()));
    }

    /**
     * A "normal" user MUST NOT be able to delete a paper.
     */
    public function testUserDeletePaper()
    {
        $paper = $this->paperGenerator->create($this->exercise, $this->bob);
        $this->om->persist($paper);
        $this->om->flush();

        $this->request('DELETE', "/api/exercises/{$this->exercise->getUuid()}/papers/{$paper->getUuid()}", $this->bob);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * An "admin" user MUST be able to delete a paper.
     */
    public function testAdminDeletePaper()
    {
        $paper = $this->paperGenerator->create($this->exercise, $this->john);
        $this->om->persist($paper);
        $this->om->flush();

        $this->request('DELETE', "/api/exercises/{$this->exercise->getUuid()}/papers/{$paper->getUuid()}", $this->john);
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(empty($this->client->getResponse()->getContent()));

        // Checks the papers have really been deleted
        $papers = $this->om->getRepository('UJMExoBundle:Attempt\Paper')->findBy([
            'exercise' => $this->exercise,
        ]);

        $this->assertCount(0, $papers);
    }

    /**
     * The api MUST NOT allow to delete a paper from a published exercise.
     */
    public function testDeletePaperThrowErrorIfExercisePublished()
    {
        $paper = $this->paperGenerator->create($this->exercise, $this->john);
        $this->om->persist($paper);

        $this->exercise->setPublishedOnce(true);
        $this->om->flush();

        $this->request('DELETE', "/api/exercises/{$this->exercise->getUuid()}/papers/{$paper->getUuid()}", $this->john);

        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());
    }

    /**
     * A "normal" user MUST NOT be able to delete the papers of an exercise.
     */
    public function testUserDeleteAllPapers()
    {
        $this->request('DELETE', "/api/exercises/{$this->exercise->getUuid()}/papers", $this->bob);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * An "admin" user MUST be able to delete all the papers of an exercise at once.
     */
    public function testAdminDeleteAllPapers()
    {
        $pa1 = $this->paperGenerator->create($this->exercise, $this->john);
        $pa2 = $this->paperGenerator->create($this->exercise, $this->john);
        $this->om->persist($pa1);
        $this->om->persist($pa2);

        $this->om->flush();

        $this->request('DELETE', "/api/exercises/{$this->exercise->getUuid()}/papers", $this->john);
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(empty($this->client->getResponse()->getContent()));

        // Checks the papers have really been deleted
        $papers = $this->om->getRepository('UJMExoBundle:Attempt\Paper')->findBy([
            'exercise' => $this->exercise,
        ]);

        $this->assertCount(0, $papers);
    }

    /**
     * The api MUST NOT allow to delete papers from a published exercise.
     */
    public function testDeleteAllPapersThrowErrorIfExercisePublished()
    {
        $this->exercise->setPublishedOnce(true);
        $this->om->flush();

        $this->request('DELETE', "/api/exercises/{$this->exercise->getUuid()}/papers", $this->john);
        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Checks the export of a paper has the correct format.
     * The paper detail MUST contain the paper itself and the list of used questions.
     *
     * @param Paper $paper
     * @param $content
     */
    private function assertIsValidPaperDetail(Paper $paper, $content)
    {
        $this->assertInstanceOf('\stdClass', $content);
        $this->assertEquals($paper->getUuid(), $content->id);
    }
}
