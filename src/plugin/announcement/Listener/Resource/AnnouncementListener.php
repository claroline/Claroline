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
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Role;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AnnouncementListener extends ResourceComponent
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud
    ) {
    }

    public static function getName(): string
    {
        return 'claroline_announcement_aggregate';
    }

    /** @var AnnouncementAggregate $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
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

        return [
            'announcement' => $this->serializer->serialize($resource),
            'posts' => $postsList['data'],
            'workspaceRoles' => array_map(function (Role $role) { // TODO : to remove. This can be retrieve directly from api later
                return $this->serializer->serialize($role, [Options::SERIALIZE_MINIMAL]);
            }, $workspace->getRoles()->toArray()),
        ];
    }

    public function update(AbstractResource $resource, array $data): ?array
    {
        return [
            'resource' => $this->serializer->serialize($resource),
        ];
    }

    /**
     * Copy all the Announces of the resource.
     *
     * @param AnnouncementAggregate $original
     * @param AnnouncementAggregate $copy
     */
    public function copy(AbstractResource $original, AbstractResource $copy): void
    {
        $this->om->startFlushSuite();

        $announcements = $original->getAnnouncements();
        foreach ($announcements as $announcement) {
            $newAnnouncement = $this->crud->copy($announcement, [
                Crud::NO_PERMISSIONS, // this has already been checked by the core before forwarding the copy
            ]);

            $newAnnouncement->setAggregate($copy);
        }

        $this->om->endFlushSuite();
    }

    /** @var AnnouncementAggregate $resource */
    public function export(AbstractResource $resource, FileBag $fileBag): ?array
    {
        return [
            'posts' => array_map(function (Announcement $announcement) {
                return $this->serializer->serialize($announcement);
            }, $resource->getAnnouncements()->toArray()),
        ];
    }

    /** @var AnnouncementAggregate $resource */
    public function import(AbstractResource $resource, FileBag $fileBag, array $data = []): void
    {
        if (empty($data['posts'])) {
            return;
        }

        $this->om->startFlushSuite();

        foreach ($data['posts'] as $announcementData) {
            $newAnnouncement = new Announcement();
            $newAnnouncement->setAggregate($resource);

            $this->crud->create($newAnnouncement, $announcementData, [
                Crud::NO_PERMISSIONS, // this has already been checked by the core before forwarding the import
                Crud::NO_VALIDATION,
                Options::REFRESH_UUID,
            ]);
        }

        $this->om->endFlushSuite();
    }

    /** @var AnnouncementAggregate $resource */
    public function delete(AbstractResource $resource, FileBag $fileBag, bool $softDelete = true): bool
    {
        if ($softDelete) {
            return true;
        }

        foreach ($resource->getAnnouncements() as $announcement) {
            $this->crud->delete($announcement);
        }

        return true;
    }
}
