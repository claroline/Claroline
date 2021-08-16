<?php

namespace UJM\ExoBundle\Tests\Controller;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\RequestTrait;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Testing\Persister;

/**
 * Tests that are common to all exercise / question types.
 */
class ExerciseControllerTest extends TransactionalTestCase
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
    /** @var Exercise */
    private $exercise;

    protected function setUp(): void
    {
        parent::setUp();

        $this->om = $this->client->getContainer()->get('Claroline\AppBundle\Persistence\ObjectManager');

        $this->persist = new Persister($this->om);
        $this->john = $this->persist->user('john');
        $this->bob = $this->persist->user('bob');

        $adminRole = $this->persist->role('ROLE_ADMIN');
        $this->admin = $this->persist->user('admin');
        $this->admin->addRole($adminRole);

        $this->exercise = $this->persist->exercise('ex1', [
            $this->persist->openQuestion('Question.'),
        ], $this->john);

        // Set up Exercise permissions
        // create 'open' mask in db
        $type = $this->exercise->getResourceNode()->getResourceType();
        $this->persist->maskDecoder($type, 'open', 1);
        $this->om->flush();

        $rightsManager = $this->client->getContainer()->get('claroline.manager.rights_manager');
        $roleManager = $this->client->getContainer()->get('claroline.manager.role_manager');

        // add open permissions to all users
        $rightsManager->update(1, $roleManager->getRoleByName('ROLE_USER'), $this->exercise->getResourceNode());

        $this->om->flush();
    }

    public function testAnonymousGet()
    {
        $this->request('GET', "/apiv2/exercises/{$this->exercise->getUuid()}");

        /*var_dump($this->client->getResponse()->getContent());
        die();*/

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testNonCreatorGet()
    {
        $this->request('GET', "/apiv2/exercises/{$this->exercise->getUuid()}", $this->bob);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminGet()
    {
        $this->request('GET', "/apiv2/exercises/{$this->exercise->getUuid()}", $this->admin);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testCreatorGet()
    {
        $this->request('GET', "/apiv2/exercises/{$this->exercise->getUuid()}", $this->john);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals($this->exercise->getUuid(), $content->id);
        $this->assertEquals('ex1', $content->title);
        $this->assertEquals('Invite...', $content->steps[0]->items[0]->content);
    }

    public function testUpdateByUser()
    {
        // Send exercise data
        $data = [];

        $this->request('PUT', "/apiv2/exercises/{$this->exercise->getUuid()}", $this->bob, [], json_encode($data));
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateByAdmin()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testUpdateWithValidData()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testUpdateWithInvalidData()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
