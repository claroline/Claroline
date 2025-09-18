<?php

namespace Claroline\ImagePlayerBundle\Component\Resource;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Resource\DownloadableResourceInterface;
use Claroline\CoreBundle\Component\Resource\FileAdapterInterface;
use Claroline\CoreBundle\Component\Resource\FileAdapterTrait;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\EvaluationBundle\Component\Resource\EvaluatedResourceInterface;
use Claroline\ImagePlayerBundle\Entity\Image;
use Claroline\ImagePlayerBundle\Manager\EvaluationManager;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class ImageResource extends ResourceComponent implements DownloadableResourceInterface, EvaluatedResourceInterface, FileAdapterInterface
{
    use FileAdapterTrait;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly SerializerProvider $serializer,
        private readonly FileManager $fileManager,
        private readonly ObjectManager $om,
        private readonly EvaluationManager $evaluationManager
    ) {
    }

    public static function getName(): string
    {
        return 'image';
    }

    public static function getClass(): string
    {
        return Image::class;
    }

    public static function supportsScore(): bool
    {
        return false;
    }

    public static function supportsAttempts(): bool
    {
        return false;
    }

    /** @param Image $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        $userEvaluation = null;
        if ($user instanceof User) {
            $userEvaluation = $this->evaluationManager->update($resource->getResourceNode(), $user);
        }

        return [
            'userEvaluation' => $userEvaluation ?
                $this->serializer->serialize($userEvaluation, [SerializerInterface::SERIALIZE_MINIMAL])
                : null,
        ];
    }

    public function supportsFile(File $file): int
    {
        if (str_starts_with($file->getMimeType(), 'image')) {
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
