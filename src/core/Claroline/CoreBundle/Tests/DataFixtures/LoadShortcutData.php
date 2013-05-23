<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadShortcutData extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * Constructor. Each key is a role name and each value is a parent role.
     *
     * @param array $roles
     */
    public function __construct(AbstractResource $target, $directory, $creator)
    {
        $this->creator = $creator;
        $this->directory = $directory;
        $this->target = $target;
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
        $resourceManager = $this->container->get('claroline.resource.manager');
        $resourceManager->makeShortcut(
            $this->target,
            $this->getReference('directory/'.$this->directory),
            $this->getReference('user/'.$this->creator)
        );
        $manager->flush();
    }
}