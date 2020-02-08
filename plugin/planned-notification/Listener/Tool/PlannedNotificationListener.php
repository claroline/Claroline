<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PlannedNotificationBundle\Listener\Tool;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\PlannedNotificationBundle\Entity\Message;
use Claroline\PlannedNotificationBundle\Entity\PlannedNotification;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PlannedNotificationListener
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;
    /** @var FinderProvider */
    private $finder;

    /**
     * PlannedNotificationListener constructor.
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param Crud                          $crud
     * @param FinderProvider                $finder
     * @param ObjectManager                 $om
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        Crud $crud,
        FinderProvider $finder,
        ObjectManager $om
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->crud = $crud;
        $this->finder = $finder;
    }

    /**
     * @param DisplayToolEvent $event
     */
    public function onWorkspaceToolOpen(DisplayToolEvent $event)
    {
        $workspace = $event->getWorkspace();

        $event->setData([
            'canEdit' => $this->authorization->isGranted(['claroline_planned_notification_tool', 'EDIT'], $workspace),
        ]);
        $event->stopPropagation();
    }

    /**
     * @todo : restore using new system
     */
    public function onWorkspaceToolCopy($event)
    {
        $oldWs = $event->getOldWorkspace();
        $workspace = $event->getNewWorkspace();

        /** @var PlannedNotification[] $planned */
        $planned = $this->finder->fetch(PlannedNotification::class, ['workspace' => $oldWs->getUuid()]);
        /** @var Message[] $oldMessages */
        $oldMessages = $this->finder->fetch(Message::class, ['workspace' => $oldWs->getUuid()]);
        $newNotifs = [];

        foreach ($planned as $old) {
            $new = $this->crud->copy($old);
            $new->setWorkspace($workspace);
            $new->emptyRoles();

            foreach ($old->getRoles() as $role) {
                foreach ($workspace->getRoles() as $wsRole) {
                    if ($wsRole->getTranslationKey() === $role->getTranslationKey()) {
                        $new->addRole($wsRole);
                    }
                }
            }
            $newNotifs[$old->getId()] = $new;
            $this->om->persist($new);
        }

        foreach ($oldMessages as $old) {
            $new = $this->crud->copy($old);
            $new->setWorkspace($workspace);
            $new->emptyNotifications();

            foreach ($old->getNotifications() as $oldNotification) {
                if (isset($newNotifs[$oldNotification->getId()])) {
                    $new->addNotification($newNotifs[$oldNotification->getId()]);
                }
            }

            $this->om->persist($new);
        }

        $this->om->flush();
    }
}
