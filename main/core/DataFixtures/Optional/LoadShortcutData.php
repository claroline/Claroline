<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Optional;

use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadShortcutData extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * Constructor. Each key is a role name and each value is a parent role.
     *
     * @param array $roles
     */
    public function __construct(ResourceNode $target, $directory, $creator, $referenceName = '')
    {
        $this->creator = $creator;
        $this->directory = $directory;
        $this->target = $target;
        $this->referenceName = $referenceName;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $resourceManager = $this->container->get('claroline.manager.resource_manager');
        $shortcut = $resourceManager->makeShortcut(
            $this->target,
            $this->getReference('directory/'.$this->directory),
            $this->getReference('user/'.$this->creator),
            new ResourceShortcut()
        );

        if ($this->referenceName !== '') {
            $this->addReference("shortcut/{$this->referenceName}", $shortcut);
        }

        $manager->flush();
    }
}
