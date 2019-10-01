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
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Event\ExportObjectEvent;
use Claroline\CoreBundle\Event\ImportObjectEvent;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AnnouncementListener
{
    use PermissionCheckerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var TwigEngine */
    private $templating;
    /** @var AnnouncementManager */
    private $manager;
    /** @var SerializerProvider */
    private $serializer;
    /** @var Crud */
    private $crud;

    /**
     * AnnouncementListener constructor.
     *
     * @param ObjectManager       $om
     * @param TwigEngine          $templating
     * @param AnnouncementManager $manager
     * @param SerializerProvider  $serializer
     * @param Crud                $crud
     */
    public function __construct(
        ObjectManager $om,
        TwigEngine $templating,
        AnnouncementManager $manager,
        SerializerProvider $serializer,
        Crud $crud,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->om = $om;
        $this->templating = $templating;
        $this->manager = $manager;
        $this->serializer = $serializer;
        $this->crud = $crud;
        $this->authorization = $authorization;
    }

    /**
     * Loads an Announcement resource.
     *
     * @param LoadResourceEvent $event
     */
    public function load(LoadResourceEvent $event)
    {
        $resource = $event->getResource();
        $workspace = $resource->getResourceNode()->getWorkspace();

        $event->setData([
            'announcement' => $this->serializer->serialize($resource),
            'workspaceRoles' => array_map(function (Role $role) {
                return $this->serializer->serialize($role, [Options::SERIALIZE_MINIMAL]);
            }, $workspace->getRoles()->toArray()),
        ]);

        $event->stopPropagation();
    }

    /**
     * @param CopyResourceEvent $event
     */
    public function copy(CopyResourceEvent $event)
    {
        /** @var AnnouncementAggregate $aggregate */
        $aggregate = $event->getResource();

        $this->om->startFlushSuite();
        $copy = $event->getCopy();
        $announcements = $aggregate->getAnnouncements();

        foreach ($announcements as $announcement) {
            $newAnnouncement = $this->manager->serialize($announcement);
            $newAnnouncement['id'] = Uuid::uuid4()->toString();
            $this->crud->create('Claroline\AnnouncementBundle\Entity\Announcement', $newAnnouncement, [
              'announcement_aggregate' => $copy,
              Options::NO_LOG => Options::NO_LOG,
            ]);
        }

        $this->om->endFlushSuite();

        $event->setCopy($copy);
        $event->stopPropagation();
    }

    public function onExport(ExportObjectEvent $exportEvent)
    {
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

    /**
     * @param DeleteResourceEvent $event
     */
    public function delete(DeleteResourceEvent $event)
    {
        $this->crud->delete($event->getResource());
        $event->stopPropagation();
    }
}
