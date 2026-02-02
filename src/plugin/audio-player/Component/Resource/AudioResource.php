<?php

namespace Claroline\AudioPlayerBundle\Component\Resource;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AudioPlayerBundle\Entity\Resource\Audio;
use Claroline\AudioPlayerBundle\Entity\Resource\Section;
use Claroline\AudioPlayerBundle\Manager\AudioPlayerManager;
use Claroline\CoreBundle\Component\Resource\DownloadableResourceInterface;
use Claroline\CoreBundle\Component\Resource\FileAdapterInterface;
use Claroline\CoreBundle\Component\Resource\FileAdapterTrait;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\FileManager;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class AudioResource extends ResourceComponent implements DownloadableResourceInterface, FileAdapterInterface
{
    use FileAdapterTrait;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly SerializerProvider $serializer,
        private readonly FileManager $fileManager,
        private readonly ObjectManager $om,
        private readonly AudioPlayerManager $manager
    ) {
    }

    public static function getName(): string
    {
        return 'audio';
    }

    public static function getClass(): string
    {
        return Audio::class;
    }

    /** @param Audio $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        /** @var User|string $user */
        $user = $this->tokenStorage->getToken()?->getUser();

        $resourceNode = $resource->getResourceNode();
        $audioData = $this->serializer->serialize($resource);

        $audioData['sections'] = [];
        switch ($resource->getSectionsType()) {
            case Audio::MANAGER_TYPE:
                $audioData['sections'] = array_values(array_map(function (Section $section) use ($user) {
                    $serializedSection = $this->serializer->serialize($section);

                    if ($user instanceof User) {
                        $userComment = $this->manager->getSectionUserComment($section, $user);

                        if ($userComment) {
                            $serializedSection['comment'] = $this->serializer->serialize($userComment);
                        }
                    }

                    return $serializedSection;
                }, $this->manager->getManagerSections($resourceNode)));
                break;

            case Audio::USER_TYPE:
                if ($user instanceof User) {
                    $audioData['sections'] = array_values(array_map(function (Section $section) use ($user) {
                        $serializedSection = $this->serializer->serialize($section);
                        $userComment = $this->manager->getSectionUserComment($section, $user);

                        if ($userComment) {
                            $serializedSection['comment'] = $this->serializer->serialize($userComment);
                        }

                        return $serializedSection;
                    }, $this->manager->getUserSections($resourceNode, $user)));
                }
                break;
        }

        return [
            'resource' => $audioData,
        ];
    }

    /** @param Audio $resource */
    public function update(AbstractResource $resource, array $data, array $previousData): ?array
    {
        $this->manager->deserializeSections($resource->getResourceNode(), $data);

        return [];
    }

    public function supportsFile(File $file): int
    {
        if (str_starts_with($file->getMimeType(), 'audio')) {
            return FileAdapterInterface::SUPPORTED;
        }

        return FileAdapterInterface::UNSUPPORTED;
    }

    public function fromFile(File $file): ?array
    {
        return [];
    }

    public function requireAdapter(): bool
    {
        return true;
    }
}
