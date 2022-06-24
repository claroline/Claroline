<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Listener\Resource;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\ExportResourceEvent;
use Claroline\CoreBundle\Event\Resource\ImportResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AnnouncementListener
{
    use PermissionCheckerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var Crud */
    private $crud;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        SerializerProvider $serializer,
        Crud $crud
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->serializer = $serializer;
        $this->crud = $crud;
    }

    /**
     * Loads an Announcement resource.
     */
    public function load(LoadResourceEvent $event)
    {
        /** @var AnnouncementAggregate $resource */
        $resource = $event->getResource();
        $workspace = $resource->getResourceNode()->getWorkspace();

        $filters = [
            'aggregate' => $resource,
        ];
        if (!$this->authorization->isGranted('EDIT', $resource->getResourceNode())) {
            $filters['visible'] = true;
        }

        $postsList = $this->crud->list(Announcement::class, [
            'filters' => $filters,
        ]);

        $event->setData([
            'announcement' => $this->serializer->serialize($resource),
            'posts' => $postsList['data'],
            'workspaceRoles' => array_map(function (Role $role) { // TODO : to remove. This can be retrieve directly from api later
                return $this->serializer->serialize($role, [Options::SERIALIZE_MINIMAL]);
            }, $workspace->getRoles()->toArray()),
        ]);

        $event->stopPropagation();
    }

    public function copy(CopyResourceEvent $event)
    {
        /** @var AnnouncementAggregate $aggregate */
        $aggregate = $event->getResource();
        /** @var AnnouncementAggregate $copy */
        $copy = $event->getCopy();

        $this->om->startFlushSuite();

        $announcements = $aggregate->getAnnouncements();
        foreach ($announcements as $announcement) {
            $announcementData = $this->serializer->serialize($announcement);

            $newAnnouncement = new Announcement();
            $newAnnouncement->setAggregate($copy);

            $this->crud->create($newAnnouncement, $announcementData, [
                Crud::NO_PERMISSIONS, // this has already been checked by the core before forwarding the copy
                Options::REFRESH_UUID,
            ]);
        }

        $this->om->endFlushSuite();

        $event->stopPropagation();
    }

    public function onExport(ExportResourceEvent $exportEvent)
    {
        /** @var AnnouncementAggregate $announcements */
        $announcements = $exportEvent->getResource();

        $exportEvent->setData([
            'posts' => array_map(function (Announcement $announcement) {
                return $this->serializer->serialize($announcement);
            }, $announcements->getAnnouncements()->toArray()),
        ]);
    }

    public function onImport(ImportResourceEvent $event)
    {
        $data = $event->getData();
        /** @var AnnouncementAggregate $announcements */
        $announcements = $event->getResource();

        $this->om->startFlushSuite();
        foreach ($data['posts'] as $announcementData) {
            $newAnnouncement = new Announcement();
            $newAnnouncement->setAggregate($announcements);

            $this->crud->create($newAnnouncement, $announcementData, [
                Crud::NO_PERMISSIONS, // this has already been checked by the core before forwarding the import
                Crud::NO_VALIDATION,
                Options::REFRESH_UUID,
            ]);
        }
        $this->om->endFlushSuite();
    }
}
