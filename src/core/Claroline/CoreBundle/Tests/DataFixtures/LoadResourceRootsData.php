<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Fixtures\LoggableFixture;
use Claroline\CoreBundle\Library\Workspace\Configuration;

class LoadResourceRootsData extends LoggableFixture implements ContainerAwareInterface
{
    /** @var ContainerInterface $container */
    private $container;
    private $rootCount;
    private $username;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function __construct($username, $rootCount)
    {
        $this->rootCount = $rootCount;
        $this->username = $username;

        $this->workspaceNames = array(
            'biology',
            'chemistry',
            'mathematic',
            'physic',
            'geography',
            'sociology',
            'informatic'
        );

        $this->workspaceNamesOffset = count($this->workspaceNames);
        $this->workspaceNamesOffset--;
    }

    public function load(ObjectManager $manager)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('ClarolineCoreBundle:User')->findOneBy(array('username' => $this->username));

        for ($i = 0; $i < $this->rootCount; $i++) {
            $config = new Configuration();
            $config->setWorkspaceType(Configuration::TYPE_SIMPLE);
            $config->setWorkspaceName($this->workspaceNames[rand(0, $this->workspaceNamesOffset)]);
            $config->setWorkspaceCode('CODE');
            $wsCreator = $this->getContainer()->get('claroline.workspace.creator');
            $wsCreator->createWorkspace($config, $user);
        }
    }
}
