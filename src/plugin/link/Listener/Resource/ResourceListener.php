<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LinkBundle\Listener\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\Resource\ResourceActionEvent;
use Claroline\LinkBundle\Entity\Resource\Shortcut;
use Doctrine\Persistence\ObjectRepository;

/**
 * Integrates the "Shortcut" resource.
 */
class ResourceListener
{
    /** @var Crud */
    private $crud;

    /** @var ObjectRepository */
    private $repository;

    public function __construct(
        ObjectManager $om,
        Crud $crud
    ) {
        $this->crud = $crud;

        $this->repository = $om->getRepository(Shortcut::class);
    }

    /**
     * Gets all shortcuts of a resource.
     */
    public function shortcuts(ResourceActionEvent $event)
    {
    }

    /**
     * Removes all linked shortcuts when a resource is deleted.
     */
    public function delete(DeleteEvent $event)
    {
        $resourceNode = $event->getObject();

        // retrieve the list of shortcuts associated to the ResourceNode
        /** @var Shortcut[] $shortcuts */
        $shortcuts = $this->repository->findBy(['target' => $resourceNode]);
        if (!empty($shortcuts)) {
            $this->crud->deleteBulk($shortcuts, $event->getOptions());
        }
    }
}
