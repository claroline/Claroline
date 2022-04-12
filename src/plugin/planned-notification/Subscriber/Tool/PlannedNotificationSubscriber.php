<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PlannedNotificationBundle\Subscriber\Tool;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\AbstractTool;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\ExportToolEvent;
use Claroline\CoreBundle\Event\Tool\ImportToolEvent;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\PlannedNotificationBundle\Entity\Message;
use Claroline\PlannedNotificationBundle\Entity\PlannedNotification as Planned;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PlannedNotificationSubscriber implements EventSubscriberInterface
{
    const NAME = 'claroline_planned_notification_tool';

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        Crud $crud
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->crud = $crud;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::getEventName(ToolEvents::OPEN, AbstractTool::WORKSPACE, static::NAME) => 'onOpen',
            ToolEvents::getEventName(ToolEvents::EXPORT, AbstractTool::WORKSPACE, static::NAME) => 'onExport',
            ToolEvents::getEventName(ToolEvents::IMPORT, AbstractTool::WORKSPACE, static::NAME) => 'onImport',
        ];
    }

    public function onOpen(OpenToolEvent $event)
    {
        $event->setData([
            // this can be retrieved from serialized tool data in ui. to remove
            'canEdit' => $this->authorization->isGranted([static::NAME, 'EDIT'], $event->getWorkspace()),
        ]);
        $event->stopPropagation();
    }

    public function onExport(ExportToolEvent $event)
    {
        $workspace = $event->getWorkspace();

        $event->setData([
            'planned' => $this->crud->list(Planned::class, ['filters' => ['workspace' => $workspace->getUuid()]])['data'],
            'messages' => $this->crud->list(Message::class, ['filters' => ['workspace' => $workspace->getUuid()]])['data'],
        ]);
    }

    public function onImport(ImportToolEvent $event)
    {
        $data = $event->getData();
        if (empty($data['messages'])) {
            return;
        }

        $this->om->startFlushSuite();
        foreach ($data['messages'] as $message) {
            $new = new Message();
            $new->setWorkspace($event->getWorkspace());

            $this->crud->create($new, $message, [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION, Options::REFRESH_UUID]);

            $event->addCreatedEntity($message['id'], $new);
        }
        $this->om->endFlushSuite();
    }
}
