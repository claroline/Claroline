<?php

namespace Claroline\UserBundle\Service\UserManager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\UserBundle\Entity\User;

class ManagerTest extends WebTestCase
{
    /**
     * @var Claroline\CoreBundle\Service\Testing\TransactionalTestClient
     */
    private $client;

    /**
     * @var Claroline\UserBundle\Service\UserManager\Manager
     */
    private $manager;

    /**
     * @var Doctrine\ORM\EntityRepository
     */
    private $repository;

    public function setUp()
    {
        $this->client = self::createClient();
        $this->manager = $this->client->getContainer()->get('claroline.user.manager');
        $this->repository = $this->client->getContainer()
                                         ->get('doctrine.orm.entity_manager')
                                         ->getRepository('Claroline\UserBundle\Entity\User');
        $this->client->beginTransaction();
    }

    public function tearDown()
    {
        $this->client->rollback();
    }

    public function testCreateThenDeleteAnUser()
    {
        $user = $this->buildTestUser();

        $this->manager->create($user);

        $users = $this->repository->findByUsername($user->getUsername());
        $this->assertEquals(1, count($users));
        $this->assertEquals($user, $users[0]);
        $this->assertTrue($users[0]->hasRole('ROLE_USER'));

        $this->manager->delete($user);

        $users = $this->repository->findByUsername($user->getUsername());
        $this->assertEquals(0, count($users));
    }

    public function testCreateAnUserWithExistingUsernameThrowsAnException()
    {
        $this->setExpectedException('Claroline\UserBundle\Service\UserManager\Exception\UserException');

        $jdoe = $this->buildTestUser();
        $copiedJdoe = $this->buildTestUser();

        $this->manager->create($jdoe);
        $this->manager->create($copiedJdoe);
    }

    private function buildTestUser()
    {
        $user = new User();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setUsername('jdoe');
        $user->setPlainPassword('123');

        return $user;
    }
}