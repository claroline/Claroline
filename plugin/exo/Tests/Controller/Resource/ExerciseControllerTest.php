<?php

namespace UJM\ExoBundle\Tests\Controller\Resource;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\RequestTrait;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
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

    public function testOpenNonExistentDocimology()
    {
        // Try to open non existent docimology
        $this->request('GET', '/exercises/1234/docimology', $this->exerciseCreator);
        // The user must have access to the exercise
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * The exercise docimology action MUST be accessible to admins of the exercise.
     */
    public function testDocimologyRendersViewToAdmin()
    {
        $this->markTestSkipped('Waiting for new resource action route to be available');
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
        $this->markTestSkipped('Waiting for new resource action route to be available');
        // Try to open docimology with a "normal" user
        $user = $this->persist->user('bob');
        $this->om->flush();

        $this->request('GET', "/exercises/{$this->exercise->getUuid()}/docimology", $user);

        // The user must not have access to the docimology
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
}
