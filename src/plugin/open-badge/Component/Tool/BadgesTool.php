<?php

namespace Claroline\OpenBadgeBundle\Component\Tool;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BadgesTool extends AbstractTool
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud,
        private readonly FileManager $fileManager
    ) {
    }

    public static function getName(): string
    {
        return 'badges';
    }

    public static function getIcon(): string
    {
        return 'trophy';
    }

    public function supportsContext(string $context): bool
    {
        return in_array($context, [
            DesktopContext::getName(),
            WorkspaceContext::getName(),
        ]);
    }

    public function getStatus(string $context, ContextSubjectInterface $contextSubject = null): ?int
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        if ($user instanceof User) {
            $countQuery = new FinderQuery();
            $countQuery->addFilter('recipient', $user->getUuid());
            if ($contextSubject) {
                $countQuery->addFilter('badge.workspace', $contextSubject->getUuid());
            }

            return $this->crud->search(Assertion::class, $countQuery)->count();
        }

        return 0;
    }

    public function export(string $context, ContextSubjectInterface $contextSubject = null, FileBag $fileBag = null): ?array
    {
        if (WorkspaceContext::getName() !== $context) {
            return [];
        }

        $badges = $this->om->getRepository(BadgeClass::class)->findBy(['workspace' => $contextSubject]);

        $badgesData = [];
        /** @var BadgeClass $badge */
        foreach ($badges as $badge) {
            $badgesData[] = $this->serializer->serialize($badge, [SerializerInterface::SERIALIZE_TRANSFER]);

            if (!empty($badge->getImage())) {
                $fileBag->add($badge->getUuid(), $badge->getImage());
            }
        }

        return [
            'badges' => $badgesData,
        ];
    }

    public function import(string $context, ContextSubjectInterface $contextSubject = null, FileBag $fileBag = null, array $data = [], array $entities = []): ?array
    {
        if (WorkspaceContext::getName() !== $context) {
            return [];
        }

        if (empty($data['badges'])) {
            return [];
        }

        $this->om->startFlushSuite();
        foreach ($data['badges'] as $badgeData) {
            if (isset($badgeData['workspace'])) {
                unset($badgeData['workspace']);
            }

            $new = new BadgeClass();
            $new->setWorkspace($contextSubject);

            $badgeImage = $fileBag->get($badgeData['id']);
            if ($badgeImage && !$this->fileManager->exists($badgeImage)) {
                $file = $this->fileManager->createFile(new File($badgeImage));
                $badgeData['image'] = $file->getUrl();
            }

            $this->crud->create($new, $badgeData, [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION, Options::REFRESH_UUID]);

            $entities[$badgeData['id']] = $new;
        }
        $this->om->endFlushSuite();

        return $entities;
    }
}
