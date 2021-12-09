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
use Claroline\AnnouncementBundle\Manager\AnnouncementManager;
use Claroline\AnnouncementBundle\Serializer\AnnouncementAggregateSerializer;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Event\ExportObjectEvent;
use Claroline\CoreBundle\Event\ImportObjectEvent;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AnnouncementListener
{
    use PermissionCheckerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var AnnouncementManager */
    private $manager;
    /** @var SerializerProvider */
    private $serializer;
    /** @var Crud */
    private $crud;

    public function __construct(
        ObjectManager $om,
        AnnouncementManager $manager,
        SerializerProvider $serializer,
        Crud $crud,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->om = $om;
        $this->manager = $manager;
        $this->serializer = $serializer;
        $this->crud = $crud;
        $this->authorization = $authorization;
    }

    /**
     * Loads an Announcement resource.
     */
    public function load(LoadResourceEvent $event)
    {
        $resource = $event->getResource();
        $workspace = $resource->getResourceNode()->getWorkspace();

        $canEdit = $this->authorization->isGranted('EDIT', $resource->getResourceNode());

        $event->setData([
            'announcement' => $this->serializer->serialize($resource, !$canEdit ? [AnnouncementAggregateSerializer::VISIBLE_POSTS_ONLY] : []),
            'workspaceRoles' => array_map(function (Role $role) {
                return $this->serializer->serialize($role, [Options::SERIALIZE_MINIMAL]);
            }, $workspace->getRoles()->toArray()),
        ]);

        $event->stopPropagation();
    }

    public function copy(CopyResourceEvent $event)
    {
        /** @var AnnouncementAggregate $aggregate */
        $aggregate = $event->getResource();

        $this->om->startFlushSuite();
        $copy = $event->getCopy();
        $announcements = $aggregate->getAnnouncements();

        foreach ($announcements as $announcement) {
            $newAnnouncement = $this->serializer->serialize($announcement);
            $newAnnouncement['id'] = Uuid::uuid4()->toString();
            $this->crud->create(Announcement::class, $newAnnouncement, [
                Crud::NO_PERMISSIONS, // this has already been checked by the core before forwarding the copy
                'announcement_aggregate' => $copy,
            ]);
        }

        $this->om->endFlushSuite();

        $event->setCopy($copy);
        $event->stopPropagation();
    }

    public function onExport(ExportObjectEvent $exportEvent)
    {
        /** @var AnnouncementAggregate $announcements */
        $announcements = $exportEvent->getObject();
        $announcePosts = $announcements->getAnnouncements()->toArray();

        $data = [
          'posts' => array_map(function (Announcement $announcement) {
              return $this->serializer->serialize($announcement);
          }, $announcePosts),
        ];

        $exportEvent->overwrite('_data', $data);
    }

    public function onImport(ImportObjectEvent $event)
    {
        $data = $event->getData();
        $announcement = $event->getObject();

        foreach ($data['posts'] as $post) {
            $announce = $this->serializer->deserialize($post, new Announcement(), [Options::REFRESH_UUID]);
            $this->om->persist($announce);
            $announce->setAggregate($announcement);
        }

        $this->om->persist($announcement);
    }
}
