<?php

namespace UJM\ExoBundle\Tests\Controller\Resource;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\RequestTrait;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Persistence\ObjectManager;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Testing\Persister;

class ExerciseControllerTest extends TransactionalTestCase
{
    use RequestTrait;

    /** @var ObjectManager */
    private $om;
    /** @var Persister */
    private $persist;

    /** @var Exercise */
    private $exercise;

    /** @var User */
    private $exerciseCreator;

    public function setUp()
    {
        parent::setUp();

        $this->om = $this->client->getContainer()->get('claroline.persistence.object_manager');
        $this->persist = new Persister($this->om);

        $this->exerciseCreator = $this->persist->user('user');
        $this->exercise = $this->persist->exercise('exercise', [], $this->exerciseCreator);

        // Set up Exercise permissions
        // create 'open' mask in db
        $type = $this->exercise->getResourceNode()->getResourceType();
        $this->persist->maskDecoder($type, 'open', 1);

        $this->om->flush();
    }

    /**
     * The exercise open action MUST throws an error to unauthorized users.
     */
    public function testOpenThrowsErrorToUnauthorizedUser()
    {
        // Try to open exercise with a "normal" user
        $user = $this->persist->user('bob');
        $this->om->flush();

        $this->request('GET', "/exercises/{$this->exercise->getUuid()}", $user);

        // The user must not have access to the exercise
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * The exercise open action MUST renders the HTML view without errors.
     */
    public function testOpenRendersViewToAuthorizedUser()
    {
        // add open permissions to all users
        $rightsManager = $this->client->getContainer()->get('claroline.manager.rights_manager');
        $roleManager = $this->client->getContainer()->get('claroline.manager.role_manager');
        $rightsManager->editPerms(1, $roleManager->getRoleByName('ROLE_USER'), $this->exercise->getResourceNode());

        // Try to open exercise with a "normal" user
        $user = $this->persist->user('bob');
        $this->om->flush();

        $crawler = $this->request('GET', "/exercises/{$this->exercise->getUuid()}", $user);

        // The user must have access to the exercise
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html')->count() > 0);
    }

    /**
     * The exercise open action MUST renders the HTML view without errors.
     */
    public function testOpenRendersViewToAuthorizedAnonymous()
    {
        // add open permissions to all users including anonymous
        $rightsManager = $this->client->getContainer()->get('claroline.manager.rights_manager');
        $roleManager = $this->client->getContainer()->get('claroline.manager.role_manager');
        $rightsManager->editPerms(1, $roleManager->getRoleByName('ROLE_ANONYMOUS'), $this->exercise->getResourceNode());
        $this->om->flush();

        $crawler = $this->request('GET', "/exercises/{$this->exercise->getUuid()}");

        // The user must not have access to the exercise
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html')->count() > 0);
    }

    /**
     * The exercise docimology action MUST be accessible to admins of the exercise.
     */
    public function testDocimologyRendersViewToAdmin()
    {
        // Try to open docimology with the creator
        $crawler = $this->request('GET', "/exercises/{$this->exercise->getUuid()}/docimology", $this->exerciseCreator);
        // The user must have access to the exercise
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html')->count() > 0);
    }

    /**
     * The exercise docimology action MUST be accessible only by admins.
     */
    public function testDocimologyThrowsErrorToUsers()
    {
        // Try to open docimology with a "normal" user
        $user = $this->persist->user('bob');
        $this->om->flush();

        $this->request('GET', "/exercises/{$this->exercise->getUuid()}/docimology", $user);

        // The user must not have access to the docimology
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
}
