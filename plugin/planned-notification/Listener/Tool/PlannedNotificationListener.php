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
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\WorkspaceCopyToolEvent;
use Claroline\PlannedNotificationBundle\Entity\Message;
use Claroline\PlannedNotificationBundle\Entity\PlannedNotification;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service
 */
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
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "om"            = @DI\Inject("Claroline\AppBundle\Persistence\ObjectManager"),
     *     "crud"          = @DI\Inject("Claroline\AppBundle\API\Crud"),
     *     "finder"        = @DI\Inject("Claroline\AppBundle\API\FinderProvider")
     * })
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
     * @DI\Observe("open_tool_workspace_claroline_planned_notification_tool")
     *
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
     * @DI\Observe("workspace_copy_tool_claroline_planned_notification_tool")
     *
     * @param WorkspaceCopyToolEvent $event
     */
    public function onWorkspaceToolCopy(WorkspaceCopyToolEvent $event)
    {
        $oldWs = $event->getOldWorkspace();
        $workspace = $event->getNewWorkspace();

        /** @var PlannedNotification[] $planned */
        $planned = $this->finder->fetch(PlannedNotification::class, ['workspace' => $oldWs->getUuid()]);
        /** @var Message[] $oldMessages */
        $oldMessages = $this->finder->fetch(Message::class, ['workspace' => $oldWs->getUuid()]);
        $newNotifs = [];

        foreach ($planned as $old) {
            $new = $this->crud->copy($old, [Options::GENERATE_UUID]);
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
            $new = $this->crud->copy($old, [Options::GENERATE_UUID]);
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
