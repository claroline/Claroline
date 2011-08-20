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

        $this->manager->delete($user);

        $users = $this->repository->findByUsername($user->getUsername());
        $this->assertEquals(0, count($users));
    }

    public function testNewlyCreatedUserHasUserRole()
    {
        $user = $this->buildTestUser();
        $this->manager->create($user);

        $user = $this->repository->findOneByUsername($user->getUsername());
        $roles = $user->getRoles();

        $this->assertEquals(1, count($roles));
        $this->assertEquals('ROLE_USER', $roles[0]->getName());
    }

    public function testCreateAnUserWithExistingUsernameThrowsAnException()
    {
        $this->setExpectedException('Claroline\UserBundle\Service\UserManager\Exception\UserException');

        $user1 = $this->buildTestUser();
        $this->assertTrue($this->manager->hasUniqueUsername($user1));
        $this->manager->create($user1);

        $user2 = $this->buildTestUser();
        $this->assertFalse($this->manager->hasUniqueUsername($user2));
        $this->manager->create($user2);
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