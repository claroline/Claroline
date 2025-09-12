<?php

namespace Claroline\AnnouncementBundle\Component\Tool;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementParameters;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\ToolComponent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;

final class AnnouncementTool extends ToolComponent
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud
    ) {
    }

    public static function getName(): string
    {
        return 'announcement';
    }

    public static function getIcon(): string
    {
        return 'bullhorn';
    }

    public function supportsContext(string $context): bool
    {
        return WorkspaceContext::getName() === $context;
    }

    public function open(OrderedTool $tool, string $context, ContextSubjectInterface $contextSubject = null): ?array
    {
        $parameters = $this->om->getRepository(AnnouncementParameters::class)->findOneByWorkspace($contextSubject);

        $postsList = $this->crud->list(Announcement::class, [
            'filters' => ['visible' => true, 'workspace' => $contextSubject->getUuid()],
        ]);

        return [
            'parameters' => $parameters ? $this->serializer->serialize($parameters) : null,
            'posts' => $postsList['data'],
        ];
    }

    public function configure(OrderedTool $tool, string $context, ?ContextSubjectInterface $contextSubject = null, array $configData = []): ?array
    {
        if (!isset($configData['parameters'])) {
            return [];
        }

        $parameters = $this->om->getRepository(AnnouncementParameters::class)->findOneByWorkspace($contextSubject);
        if (empty($parameters)) {
            $parameters = new AnnouncementParameters();
            $parameters->setOrderedTool($tool);
        }

        $this->serializer->deserialize($configData['parameters'], $parameters, [SerializerInterface::REFRESH_UUID]);

        $this->om->persist($parameters);
        $this->om->flush();

        return [
            'parameters' => $this->serializer->serialize($parameters),
        ];
    }

    public function export(string $context, ?ContextSubjectInterface $contextSubject = null, FileBag $fileBag = null): ?array
    {
        if (WorkspaceContext::getName() !== $context) {
            return [];
        }

        $announcements = $this->om->getRepository(Announcement::class)->findBy(['workspace' => $contextSubject]);

        return [
            'announcements' => array_map(function (Announcement $announcement) {
                return $this->serializer->serialize($announcement);
            }, $announcements),
        ];
    }

    public function import(string $context, ?ContextSubjectInterface $contextSubject = null, FileBag $fileBag = null, array $data = [], array $entities = []): ?array
    {
        if (WorkspaceContext::getName() !== $context) {
            return [];
        }

        if (empty($data['announcements'])) {
            return null;
        }

        $this->om->startFlushSuite();

        foreach ($data['announcements'] as $announcementData) {
            $newAnnouncement = new Announcement();
            $newAnnouncement->setWorkspace($contextSubject);

            $this->crud->create($newAnnouncement, $announcementData, [
                Crud::NO_PERMISSIONS, // the core has already checked this before forwarding the import
                Crud::NO_VALIDATION,
                Options::REFRESH_UUID,
            ]);

            $entities[$announcementData['id']] = $newAnnouncement;
        }

        $this->om->endFlushSuite();

        return $entities;
    }
}
