<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LinkBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\LinkBundle\Entity\Resource\Shortcut;
use Doctrine\Common\Persistence\ObjectRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Manages resource shortcuts.
 *
 * @DI\Service("claroline.manager.shortcut")
 */
class ShortcutManager
{
    /** @var ObjectManager */
    private $om;

    /** @var ObjectRepository */
    private $repository;

    /**
     * ShortcutManager constructor.
     *
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repository = $this->om->getRepository('ClarolineLinkBundle:Resource\Shortcut');
    }

    /**
     * Removes all shortcuts associated to a resource.
     *
     * @param ResourceNode $resourceNode
     *
     * @todo delete through the normal resource lifecycle
     */
    public function removeShortcutsTo(ResourceNode $resourceNode)
    {
        $this->om->startFlushSuite();

        // retrieve the list of shortcuts associated to the ResourceNode
        /** @var Shortcut[] $shortcuts */
        $shortcuts = $this->repository->findBy(['target' => $resourceNode]);

        foreach ($shortcuts as $shortcut) {
            $this->om->remove($shortcut->getResourceNode());
        }
        $this->om->endFlushSuite();
    }
}
