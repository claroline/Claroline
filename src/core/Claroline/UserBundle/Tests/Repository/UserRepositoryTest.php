<?php

namespace Claroline\UserBundle\Repository;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\UserBundle\Entity\User;
use Claroline\Lib\Testing\TransactionalTestCase;

class UserRepositoryTest extends TransactionalTestCase
{
    
    /** @var Doctrine\ORM\EntityManager */
    private $em;
    
    /** @var Claroline\UserBundle\Service\UserManager\Manager */    
    private $userManager;
    
    /** @var UserRepository */
    private $userRepo;
    
    public function setUp()
    {
        parent :: setUp();
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->userManager = $this->client->getContainer()->get('claroline.user.manager');
        $this->userRepo = $this->em->getRepository('Claroline\UserBundle\Entity\User');
        
    }
    
    public function testGetUsersByUsernameListReturnsExpectedResults()
    {
        $john = $this->createUser('John', 'Doe', 'jdoe', '123');
        $mike = $this->createUser('Mike', 'Doe', 'mdoe', '123');
        $bill = $this->createUser('Bill', 'Doe', 'bdoe', '123');
        $rick = $this->createUser('Rick', 'Doe', 'rdoe', '123');
        
        $users = $this->userRepo->getUsersByUsernameList(array('jdoe', 'mdoe', 'bdoe', 'rdoe'));
        
        $this->assertEquals(4, count($users));
        $this->assertEquals($bill, $users[0]);
        $this->assertEquals($john, $users[1]);
        $this->assertEquals($mike, $users[2]);
        $this->assertEquals($rick, $users[3]);
    }
    
    private function createUser($firstName, $lastName, $username, $password)
    {
        $user = new User();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setUserName($username);
        $user->setPlainPassword($password);
        
        $this->userManager->create($user);
        
        return $user;
    }
}