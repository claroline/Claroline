<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Claroline\CoreBundle\Library\Workspace\Configuration;

class LoadWorkspaceData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /** @var ContainerInterface $container */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
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
        $admin = $this->getReference('user/admin');
        $wsCreator = $this->getReference('user/ws_creator');

        $wsA = $this->createSimpleWorkspace('Workspace_A', $wsCreator);
        $wsB = $this->createSimpleWorkspace('Workspace_B', $wsCreator);
        $wsC = $this->createSimpleWorkspace('Workspace_C', $wsCreator);
        $wsD = $this->createSimpleWorkspace('Workspace_D', $wsCreator);
        $wsE = $this->createSimpleWorkspace('Workspace_E', $admin);
        $wsF = $this->createSimpleWorkspace('Workspace_F', $admin);

        $wsD->setPublic(false);
        $wsE->setPublic(false);
        $wsF->setPublic(false);

        $wsB->setParent($wsA);
        $wsC->setParent($wsA);
        $wsD->setParent($wsC);
        $wsE->setParent($wsC);
        $wsF->setParent($wsE);

        $manager->persist($wsA);
        $manager->persist($wsB);
        $manager->persist($wsC);
        $manager->persist($wsD);
        $manager->persist($wsE);
        $manager->persist($wsF);
        $manager->flush();

        $this->addReference('workspace/ws_a', $wsA);
        $this->addReference('workspace/ws_b', $wsB);
        $this->addReference('workspace/ws_c', $wsC);
        $this->addReference('workspace/ws_d', $wsD);
        $this->addReference('workspace/ws_e', $wsE);
        $this->addReference('workspace/ws_f', $wsF);
    }

    private function createSimpleWorkspace($name, $user)
    {
        $wsCreator = $this->container->get('claroline.workspace.creator');
        $config = new Configuration();
        $config->setWorkspaceName($name);
        $ws = $wsCreator->createWorkspace($config, $user);

        return $ws;
    }

    public function getOrder()
    {
        return 4;
    }
}