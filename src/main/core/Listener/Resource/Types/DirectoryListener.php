<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Resource\Types;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\CoreBundle\Component\Resource\DownloadableResourceInterface;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Manager\ResourceManager;

/**
 * Integrates the "Directory" resource.
 */
final class DirectoryListener extends ResourceComponent implements DownloadableResourceInterface
{
    public function __construct(
        private readonly Crud $crud,
        private readonly ResourceManager $resourceManager,
    ) {
    }

    public static function getName(): string
    {
        return 'directory';
    }

    /** @param Directory $resource */
    public function download(AbstractResource $resource, FileBag $fileBag): void
    {
        $childrenBag = new FileBag();
        $children = $resource->getResourceNode()->getChildren()->toArray();
        if (!empty($children)) {
            $this->resourceManager->download($children, $childrenBag);
        }

        if (0 !== $childrenBag->count()) {
            foreach ($childrenBag->all() as $fileName => $filePath) {
                $fileBag->add($resource->getName().'/'.$fileName, $filePath);
            }
        }
    }

    /** @param Directory $resource */
    public function delete(AbstractResource $resource, FileBag $fileBag, bool $softDelete = true): bool
    {
        // delete all children of the current directory
        // this may be interesting to put it in the messenger bus
        $resourceNode = $resource->getResourceNode();

        if (!empty($resourceNode->getChildren())) {
            $this->crud->deleteBulk($resourceNode->getChildren()->toArray(), $softDelete ? [Options::SOFT_DELETE] : []);
        }

        return true;
    }

    /** @param Directory $original */
    public function copy(AbstractResource $original, AbstractResource $copy): void
    {
        $resourceNode = $original->getResourceNode();
        if (0 === $resourceNode->getChildren()->count()) {
            return;
        }

        foreach ($resourceNode->getChildren() as $child) {
            if ($child->isActive()) {
                $this->crud->copy($child, [Options::NO_RIGHTS, Crud::NO_PERMISSIONS], ['parent' => $copy->getResourceNode()]);
            }
        }
    }
}
