<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Workspace\Configuration;

class LoadWorkspaceData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /** @var ContainerInterface $container */
    private $container;

    private $workspaceNames;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function __construct($workspaceNames = null)
    {
        if ($workspaceNames !== null)
        {
            $this->workspaceNames = $workspaceNames;
        } else {
            $this->workspaceNames = array('ws_a', 'ws_b', 'ws_c', 'ws_d', 'ws_e', 'ws_f');
        }
    }

    /**
     * Creates a simple workspace with the following structure :
     *
     * Workspace A              (public)
     *      Workspace B         (public)
     *      Workspace C         (public)
     *          Workspace D     (private)
     *          Workspace E     (private)
     *              Workspace F (private)
     */
    public function load(ObjectManager $manager)
    {
        $workspaces = array(
            'ws_a' => array('Workspace_A', 'ws_creator', 'wsA', true, null),
            'ws_b' => array('Workspace_B', 'ws_creator', 'wsB', true, 'ws_a'),
            'ws_c' => array('Workspace_C', 'ws_creator', 'wsC', true, 'ws_a'),
            'ws_d' => array('Workspace_D', 'ws_creator', 'wsD', false, 'ws_c'),
            'ws_e' => array('Workspace_E', 'admin', 'wsE', false, 'ws_c'),
            'ws_f' => array('Workspace_F', 'admin', 'wsF', false, 'ws_e')
        );

        foreach ($this->workspaceNames as $workspaceName){
            if(array_key_exists($workspaceName, $workspaces)){
                $ws = $this->createSimpleWorkspace($workspaces[$workspaceName][0], $this->getReference('user/'.$workspaces[$workspaceName][1]), $workspaces[$workspaceName][2]);
                $ws->setPublic($workspaces[$workspaceName][4]);
                $manager->persist($ws);
                $this->addReference('workspace/'.$workspaceName, $ws);
            }

            $manager->flush();
        }
    }

    private function createSimpleWorkspace($name, $user, $code)
    {
        $wsCreator = $this->container->get('claroline.workspace.creator');
        $config = new Configuration();
        $config->setWorkspaceName($name);
        $config->setWorkspaceCode($code);
        $ws = $wsCreator->createWorkspace($config, $user);

        return $ws;
    }

    public function getOrder()
    {
        return 4;
    }
}