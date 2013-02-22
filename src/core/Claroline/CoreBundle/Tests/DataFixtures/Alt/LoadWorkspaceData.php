<?php

namespace Claroline\CoreBundle\Tests\DataFixtures\Alt;

use Claroline\CoreBundle\Library\Workspace\Configuration;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadWorkspaceData extends AbstractFixture implements ContainerAwareInterface
{
    private $workspaces;

    /**
     * Constructor. Expects an associative array where each key is an unique workspace name
     * and each value is the creator's username. Users must have been loaded
     * and referenced in a previous fixtures with a 'user/[username]' label.
     *
     * For each group, 1 fixture reference will be added:
     * - role/[group's name] (group's role)
     *
     * @param array $users
     */
    public function __construct(array $workspaces)
    {
        $this->workspaces = $workspaces;
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $workspaceCreator = $this->container->get('claroline.workspace.creator');

        foreach($this->workspaces as $name => $username) {
            $config = new Configuration();
            $config->setWorkspaceName($name);
            $config->setWorkspaceCode($name);
            $config->setWorkspaceType(Configuration::TYPE_SIMPLE);
            $workspace = $workspaceCreator->createWorkspace($config, $this->getReference('user/'.$username));
            $this->setReference('workspace/'.$name, $workspace);
        }
    }
}

