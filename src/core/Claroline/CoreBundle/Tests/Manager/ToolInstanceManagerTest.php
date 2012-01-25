<?php

namespace Claroline\CoreBundle\Manager;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Entity\ToolInstance;
use Claroline\CoreBundle\Entity\Tool;
use Claroline\CoreBundle\Entity\Workspace;

class ToolInstanceTest extends FunctionalTestCase
{
    /** @var Claroline\CoreBundle\Manager\ToolInstanceManager\ */
    private $manager;
    
    /** @var EntityManager */
    protected $em;

    /** @var Doctrine\ORM\EntityRepository */
    private $repository;
    
    /** @var Claroline\CoreBundle\Entity\Workspace */
    private $workspace;
    
    /** @var array[User] */
    private $users;
    
    /** @var Claroline\CoreBundle\Entity\tool */
    private $tool;   

    public function setUp()
    {
        parent::setUp();
        $this->manager = $this->client->getContainer()->get('claroline.workspace.toolInstance_manager');
        $this->repository = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\ToolInstance');
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->users = $this->loadUserFixture();
        
        $this->initTestWorkspace();
        $this->initTestTool();
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
        
        $this->logUser($this->users['user']);
        
        $this->assertTrue($this->getSecurityContext()->isGranted('VIEW', $toolInstance));
        $this->assertFalse($this->getSecurityContext()->isGranted('EDIT', $toolInstance));
        $this->assertFalse($this->getSecurityContext()->isGranted('DELETE', $toolInstance));
    }
       
    private function initTestWorkspace()
    {
        $this->workspace = new Workspace();
        $this->workspace->setName('Workspace Test');
        $this->em->persist($this->workspace);
        $this->em->flush($this->workspace);
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
}