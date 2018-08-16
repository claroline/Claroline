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

use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\AnnouncementBundle\Manager\AnnouncementManager;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\OpenResourceEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;

/**
 * @DI\Service()
 */
class AnnouncementListener
{
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
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "templating" = @DI\Inject("templating"),
     *     "manager"    = @DI\Inject("claroline.manager.announcement_manager"),
     *     "serializer" = @DI\Inject("claroline.api.serializer"),
     *     "crud"       = @DI\Inject("claroline.api.crud")
     * })
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
        Crud $crud
    ) {
        $this->om = $om;
        $this->templating = $templating;
        $this->manager = $manager;
        $this->serializer = $serializer;
        $this->crud = $crud;
    }

    /**
     * Loads an Announcement resource.
     *
     * @DI\Observe("resource.claroline_announcement_aggregate.load")
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
     * @DI\Observe("open_claroline_announcement_aggregate")
     *
     * @param OpenResourceEvent $event
     */
    public function open(OpenResourceEvent $event)
    {
        $resource = $event->getResource();
        $workspace = $resource->getResourceNode()->getWorkspace();

        $content = $this->templating->render(
            'ClarolineAnnouncementBundle:announcement:open.html.twig', [
                '_resource' => $resource,
                'announcement' => $this->serializer->serialize($resource),
                'roles' => array_map(function (Role $role) {
                    return $this->serializer->serialize($role, [Options::SERIALIZE_MINIMAL]);
                }, $workspace->getRoles()->toArray()),
            ]
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_claroline_announcement_aggregate")
     *
     * @param CopyResourceEvent $event
     */
    public function copy(CopyResourceEvent $event)
    {
        /** @var AnnouncementAggregate $aggregate */
        $aggregate = $event->getResource();

        $this->om->startFlushSuite();
        $copy = new AnnouncementAggregate();
        $this->om->persist($copy);

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

    /**
     * @DI\Observe("delete_claroline_announcement_aggregate")
     *
     * @param DeleteResourceEvent $event
     */
    public function delete(DeleteResourceEvent $event)
    {
        $event->stopPropagation();
    }
}
