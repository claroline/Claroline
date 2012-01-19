<?php

namespace Claroline\CoreBundle\Manager;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Claroline\CoreBundle\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Entity\Workspace;
use Claroline\CoreBundle\Tests\DataFixtures\ORM\LoadUserData;

class WorkspaceUserManagerTest extends TransactionalTestCase
{
    /** @var Claroline\CoreBundle\Entity\Workspace */
    private $workspace;
    
    /** @var array[User] */
    private $users;
    
    /** @var Claroline\CoreBundle\Manager\UserManager */
    private $userManager;
    
    public function setUp()
    {
        parent::setUp();
        $container = $this->client->getContainer();
        $this->initTestWorkspace($container->get('doctrine.orm.entity_manager'));
        $this->initTestUsers($container->get('doctrine.orm.entity_manager'));
        $this->userManager = $container->get('claroline.workspace.user_manager');
    }
    
    public function testAnUserCanBeAddedAndRetrieved()
    {
        $this->assertEquals(0, count($this->workspace->getUsers()));
        $this->userManager->addUser($this->workspace, $this->users['user']);
        $this->assertEquals(1, count($this->workspace->getUsers()));
    }
    
    public function testAnUserCanBeRemoved()
    {
        $this->userManager->addUser($this->workspace, $this->users['admin']);
        $this->userManager->addUser($this->workspace, $this->users['user']);
        $this->assertEquals(2, count($this->workspace->getUsers()));
        $this->userManager->removeUser($this->workspace, $this->users['admin']);
        $this->assertEquals(1, count($this->workspace->getUsers()));
    }
    
    public function testAddedUserHasViewRightByDefault()
    {
        $this->userManager->addUser($this->workspace, $this->users['user']);
        $this->logUser('user', '123');
        $this->assertTrue($this->getSecurityContext()->isGranted('VIEW', $this->workspace));
    }
    
    public function testAnUserCanBeAddedWithSpecificPermissions()
    {
        $mb = new MaskBuilder();
        $mask = $mb->add(MaskBuilder::MASK_VIEW)->add(MaskBuilder::MASK_DELETE)->get();
        $this->userManager->addUser($this->workspace, $this->users['user'], $mask);
        $this->logUser('user', '123');
        $this->assertTrue($this->getSecurityContext()->isGranted('VIEW', $this->workspace));
        $this->assertFalse($this->getSecurityContext()->isGranted('EDIT', $this->workspace));
        $this->assertTrue($this->getSecurityContext()->isGranted('DELETE', $this->workspace));
    }
    
    public function testRemovedUserLoosesHisRightsOnWorkspace()
    {
        $this->userManager->addUser($this->workspace, $this->users['user'], MaskBuilder::MASK_EDIT);
        $this->userManager->removeUser($this->workspace, $this->users['user']);
        $this->logUser('user', '123');
        $this->assertFalse($this->getSecurityContext()->isGranted('VIEW', $this->workspace));
        $this->assertFalse($this->getSecurityContext()->isGranted('EDIT', $this->workspace));
    }
    
    private function initTestWorkspace($entityManager)
    {
        $this->workspace = new Workspace();
        $this->workspace->setName('Workspace Test');
        $entityManager->persist($this->workspace);
        $entityManager->flush($this->workspace);
    }
    
    private function initTestUsers($entityManager)
    {
        $refRepo = new ReferenceRepository($entityManager);
        $userFixture = new LoadUserData();
        $userFixture->setContainer($this->client->getContainer());
        $userFixture->setReferenceRepository($refRepo);
        $this->users = $userFixture->load($entityManager);
    }
       
    private function logUser($username, $password)
    {
        $this->client->request('GET', '/logout');
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->filter('#login_form input[type=submit]')->form();
        $form['_username'] = $username;
        $form['_password'] = $password;
        $this->client->submit($form);
    }
    
    private function getSecurityContext()
    {
        return $this->client->getContainer()->get('security.context');
    }
}