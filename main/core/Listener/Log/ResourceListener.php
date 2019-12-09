<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Log;

use Claroline\AppBundle\Event\Crud\CopyEvent;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Repository\UserRepository;

class ResourceListener
{
    /** @var StrictDispatcher */
    private $dispatcher;

    /** @var ObjectManager */
    private $om;

    /** @var UserRepository */
    private $userRepo;

    public function __construct(StrictDispatcher $dispatcher, ObjectManager $om)
    {
        $this->dispatcher = $dispatcher;
        $this->om = $om;

        $this->userRepo = $this->om->getRepository(User::class);
    }

    public function onResourceLoad(LoadResourceEvent $event)
    {
        $this->dispatcher->dispatch('log', 'Log\LogResourceRead', [$event->getResourceNode(), $event->isEmbedded()]);
    }

    public function onResourceCreate(CreateEvent $event)
    {
        /** @var ResourceNode $node */
        $node = $event->getObject();
        $workspace = $node->getWorkspace();
        $usersToNotify = $workspace ?
            $this->userRepo->findUsersByWorkspaces([$workspace]) :
            [];

        $this->dispatcher->dispatch('log', 'Log\LogResourceCreate', [$node, $usersToNotify]);
    }

    public function onResourceDelete(DeleteEvent $event)
    {
        $node = $event->getObject();

        $this->dispatcher->dispatch('log', 'Log\LogResourceDelete', [$node]);
    }

    public function onResourceCopy(CopyEvent $event)
    {
        $node = $event->getObject();
        $newNode = $event->getCopy();

        $this->dispatcher->dispatch('log', 'Log\LogResourceCopy', [$newNode, $node]);
    }

    public function onResourceUpdate(UpdateEvent $event)
    {
        $node = $event->getObject();
        $uow = $this->om->getUnitOfWork();
        $uow->computeChangeSets();
        $changeSet = $uow->getEntityChangeSet($node);

        $old = $event->getOldData();

        if ($old['meta']['published'] !== $node->isPublished() && $node->isPublished()) {
            $workspace = $node->getWorkspace();
            $usersToNotify = $node->getWorkspace() && !$node->getWorkspace()->isDisabledNotifications() ?
                $this->om->getRepository(User::class)->findUsersByWorkspaces([$workspace]) :
                [];
            $this->dispatcher->dispatch('log', 'Log\LogResourcePublish', [$node, $usersToNotify]);
        }

        if (count($changeSet) > 0) {
            $this->dispatcher->dispatch('log', 'Log\LogResourceUpdate', [$node, $changeSet]);
        }
    }
}
