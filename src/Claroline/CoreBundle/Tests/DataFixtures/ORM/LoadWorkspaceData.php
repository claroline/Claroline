<?php
namespace Claroline\CoreBundle\Tests\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Claroline\CoreBundle\Entity\Workspace;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadWorkspaceData extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
{
    /** @var ContainerInterface $container */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    
    public function load($manager)
    {
        $admin = $manager->merge($this->getReference('user/admin'));
        $ws_manager = $this->container->get('claroline.core.acl_workspace_manager');
        
        for($i = 0; $i < 10; ++$i)
        {
            $workspace = new Workspace();
            $workspace->setName("test workspace #{$i}");
            $workspace->setOwner($admin);
            $ws_manager->create($workspace);
        }

    }

    public function getOrder() {
        return 11; // the order in which fixtures will be loaded
    }
}
