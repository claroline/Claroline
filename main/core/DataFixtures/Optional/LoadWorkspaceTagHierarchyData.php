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

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Fixtures\LoggableFixture;

class LoadWorkspaceTagHierarchyData extends LoggableFixture implements ContainerAwareInterface
{
    private $container;
    private $tagHierarchies;

    public function __construct(array $tagHierarchies)
    {
        $this->tagHierarchies = $tagHierarchies;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        foreach ($this->tagHierarchies as $data) {
            $child = $this->getReference('tag/' . $data['child']);
            $parent = $this->getReference('tag/' . $data['parent']);

            $this->container->get('claroline.manager.workspace_tag_manager')->createTagHierarchy(
                $child,
                $parent,
                $data['level']
            );
        }
        $manager->flush();
    }
}
