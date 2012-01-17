<?php

namespace Claroline\WorkspaceBundle\Manager;

use Claroline\PluginBundle\Entity\ToolInstance;
use Claroline\CommonBundle\Test\TransactionalTestCase;
use Claroline\PluginBundle\Entity\Tool;
use Claroline\WorkspaceBundle\Entity\Workspace;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Claroline\UserBundle\Tests\DataFixtures\ORM\LoadUserData;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class ToolInstanceTest extends TransactionalTestCase
{
    /** @var Claroline\WorkspaceBundle\Manager\ToolInstanceManager\ */
    private $manager;
    
    /** @var EntityManager    */
    protected $em;

    /** @var Doctrine\ORM\EntityRepository */
    private $repository;
    
    /** @var Claroline\WorkspaceBundle\Entity\Workspace */
    private $workspace;
    
    /** @var array[User] */
    private $users;
    
    /** @var Claroline\PluginBundle\Entity\tool */
    private $tool;   

    public function setUp()
    {
        parent::setUp();
        $this->manager = $this->client->getContainer()->get('claroline.workspace.toolInstance_manager');
        $this->repository = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\PluginBundle\Entity\ToolInstance');
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $this->initTestWorkspace();
        $this->initTestUsers();
        $this->initTestTool();
        }
    private function initTestWorkspace()
    {
        $this->workspace = new Workspace();
        $this->workspace->setName('Workspace Test');
        $this->em->persist($this->workspace);
        $this->em->flush($this->workspace);
    }
    
    private function initTestUsers()
    {
        $refRepo = new ReferenceRepository($this->em);
        $userFixture = new LoadUserData();
        $userFixture->setContainer($this->client->getContainer());
        $userFixture->setReferenceRepository($refRepo);
        $this->users = $userFixture->load($this->em);
    }
    
    private function initTestTool()
    {
        $this->tool = new Tool();
        $this->tool->setType('articleType');
        $this->tool->setBundleFQCN('articleFQCN');
        $this->tool->setBundleName('article');
        $this->tool->setVendorName('articleVendor');
        $this->tool->setNameTranslationKey('fr');
        $this->tool->setDescriptionTranslationKey('article');
        
        $this->em->persist($this->tool);
        $this->em->flush($this->tool);
    }
    
    public function testCreateThenDeleteAnToolInstance()
    {   
        $toolInstance = new ToolInstance;
        $toolInstance = $this->manager->create($this->tool, $this->workspace);
        
        $instances = $this->repository->findAll();
        $this->assertEquals(1, count($instances));

        $tools = $this->workspace->getTools();
        $this->assertEquals(1, count($tools));
        $this->assertEquals('article', $tools[0]->getToolType()->getBundleName());
        
        $this->manager->delete($toolInstance, $this->workspace);

        $instances = $this->repository->findAll();
        $this->assertEquals(0, count($instances));
    }
    
    public function testSetPermission()
    {
        $toolInstance = new ToolInstance;
        $toolInstance = $this->manager->create($this->tool, $this->workspace);
        $this->manager->setPermission($toolInstance, $this->users['user'], MaskBuilder::MASK_VIEW);
        
        $this->logUser('user', '123');
        
        $this->assertTrue($this->client->getContainer()->get('security.context')
                    ->isGranted('VIEW', $toolInstance));
        $this->assertFalse($this->client->getContainer()->get('security.context')
                    ->isGranted('EDIT', $toolInstance));
        $this->assertFalse($this->client->getContainer()->get('security.context')
                    ->isGranted('DELETE', $toolInstance));
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
    
    
}