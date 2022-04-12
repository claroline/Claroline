<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Subscriber\Tool;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\AbstractTool;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\ExportToolEvent;
use Claroline\CoreBundle\Event\Tool\ImportToolEvent;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Badge tool.
 */
class BadgesSubscriber implements EventSubscriberInterface
{
    const NAME = 'badges';

    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var Crud */
    private $crud;

    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer,
        Crud $crud
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->crud = $crud;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::getEventName(ToolEvents::OPEN, AbstractTool::DESKTOP, static::NAME) => 'onOpenDesktop',
            ToolEvents::getEventName(ToolEvents::OPEN, AbstractTool::WORKSPACE, static::NAME) => 'onOpenWorkspace',
            ToolEvents::getEventName(ToolEvents::EXPORT, AbstractTool::WORKSPACE, static::NAME) => 'onExportWorkspace',
            ToolEvents::getEventName(ToolEvents::IMPORT, AbstractTool::WORKSPACE, static::NAME) => 'onImportWorkspace',
        ];
    }

    public function onOpenDesktop(OpenToolEvent $event)
    {
        $event->setData([]);

        $event->stopPropagation();
    }

    public function onOpenWorkspace(OpenToolEvent $event)
    {
        $event->setData([]);

        $event->stopPropagation();
    }

    public function onExportWorkspace(ExportToolEvent $event)
    {
        $badges = $this->om->getRepository(BadgeClass::class)->findBy(['workspace' => $event->getWorkspace()]);

        // TODO : add files

        $event->setData([
            'badges' => array_map(function (BadgeClass $badge) {
                return $this->serializer->serialize($badge);
            }, $badges),
        ]);
    }

    public function onImportWorkspace(ImportToolEvent $event)
    {
        $data = $event->getData();
        if (empty($data['badges'])) {
            return;
        }

        $this->om->startFlushSuite();
        foreach ($data['badges'] as $badgeData) {
            if (isset($badgeData['workspace'])) {
                unset($badgeData['workspace']);
            }

            $new = new BadgeClass();
            $new->setWorkspace($event->getWorkspace());

            $this->crud->create($new, $badgeData, [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION, Options::REFRESH_UUID]);

            $event->addCreatedEntity($badgeData['id'], $new);
        }
        $this->om->endFlushSuite();
    }
}
