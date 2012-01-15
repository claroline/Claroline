<?php

namespace Claroline\WorkspaceBundle\Manager;

use Claroline\PluginBundle\Entity\ToolInstance;
use Claroline\CommonBundle\Test\TransactionalTestCase;
use Claroline\PluginBundle\Entity\Tool;
use Claroline\WorkspaceBundle\Entity\Workspace;

class ToolInstanceTest extends TransactionalTestCase
{
    /** @var Claroline\WorkspaceBundle\Manager\ToolInstanceManager\ */
    private $manager;

    /** @var Doctrine\ORM\EntityRepository */
    private $repository;

    public function setUp()
    {
        parent::setUp();
        $this->manager = $this->client->getContainer()->get('claroline.workspace.toolInstance_manager');
        $this->repository = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\PluginBundle\Entity\ToolInstance');
        
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
 
    }

    public function testCreateThenDeleteAnToolInstance()
    {
    
        $tool = new Tool();
        $tool->setType('articleType');
        $tool->setBundleFQCN('articleFQCN');
        $tool->setBundleName('article');
        $tool->setVendorName('articleVendor');
        $tool->setNameTranslationKey('fr');
        $tool->setDescriptionTranslationKey('article');
        $this->em->persist($tool);
        
        $ws = new Workspace();
        $ws->setName('test');
        $this->em->persist($ws);
        
        $toolInstance = $this->manager->create($tool,$ws);
        
        $instances = $this->repository->findAll();
        $this->assertEquals(1, count($instances));

        $tools = $ws->getTools();
        $this->assertEquals(1, count($tools));
        $this->assertEquals('article',$tools[0]->getToolType()->getBundleName());
        
        $this->manager->delete($toolInstance,$ws);

        $instances = $this->repository->findAll();
        $this->assertEquals(0, count($instances));
    }
    
    
}