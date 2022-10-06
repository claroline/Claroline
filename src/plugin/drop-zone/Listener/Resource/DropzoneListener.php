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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Manager\DropzoneManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DropzoneListener
{
    /** @var string */
    private $filesDir;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var DropzoneManager */
    private $dropzoneManager;

    public function __construct(
        string $filesDir,
        TokenStorageInterface $tokenStorage,
        DropzoneManager $dropzoneManager
    ) {
        $this->filesDir = $filesDir;
        $this->tokenStorage = $tokenStorage;
        $this->dropzoneManager = $dropzoneManager;
    }

    public function onLoad(LoadResourceEvent $event)
    {
        /** @var Dropzone $dropzone */
        $dropzone = $event->getResource();

        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user instanceof User) {
            $user = null;
        }

        $event->setData(
            $this->dropzoneManager->getDropzoneData($dropzone, $user)
        );
        $event->stopPropagation();
    }

    public function onCopy(CopyResourceEvent $event)
    {
        /** @var Dropzone $dropzone */
        $dropzone = $event->getResource();
        /** @var Dropzone $copy */
        $copy = $event->getCopy();

        $copy = $this->dropzoneManager->copyDropzone($dropzone, $copy);

        $event->setCopy($copy);
        $event->stopPropagation();
    }

    public function onDelete(DeleteResourceEvent $event)
    {
        /** @var Dropzone $dropzone */
        $dropzone = $event->getResource();

        $dropzoneDir = $this->filesDir.DIRECTORY_SEPARATOR.'dropzone'.DIRECTORY_SEPARATOR.$dropzone->getUuid();
        if (file_exists($dropzoneDir)) {
            $event->setFiles([$dropzoneDir]);
        }

        $event->stopPropagation();
    }
}
