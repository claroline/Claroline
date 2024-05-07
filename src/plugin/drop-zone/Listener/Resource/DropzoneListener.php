<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Listener\Resource;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Manager\DropzoneManager;
use Claroline\EvaluationBundle\Component\Resource\EvaluatedResourceInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DropzoneListener extends ResourceComponent implements EvaluatedResourceInterface
{
    public function __construct(
        private readonly string $filesDir,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly DropzoneManager $dropzoneManager
    ) {
    }

    public static function getName(): string
    {
        return 'claroline_dropzone';
    }

    /** @var Dropzone $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user instanceof User) {
            $user = null;
        }

        return $this->dropzoneManager->getDropzoneData($resource, $user);
    }

    /**
     * @param Dropzone $original
     * @param Dropzone $copy
     */
    public function copy(AbstractResource $original, AbstractResource $copy): void
    {
        $this->dropzoneManager->copyDropzone($original, $copy);
    }

    /** @var Dropzone $resource */
    public function delete(AbstractResource $resource, FileBag $fileBag, bool $softDelete = true): bool
    {
        if ($softDelete) {
            return true;
        }

        $dropzoneDir = $this->filesDir.DIRECTORY_SEPARATOR.'dropzone'.DIRECTORY_SEPARATOR.$resource->getUuid();
        if (file_exists($dropzoneDir)) {
            $fileBag->add($resource->getUuid(), $dropzoneDir);
        }

        return true;
    }
}
