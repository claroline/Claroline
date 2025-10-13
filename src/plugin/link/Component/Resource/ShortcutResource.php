<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LinkBundle\Component\Resource;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\CoreBundle\Component\Resource\DownloadableResourceInterface;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\EvaluationBundle\Component\Resource\EvaluatedResourceInterface;
use Claroline\LinkBundle\Entity\Resource\Shortcut;

/**
 * Integrates the "Shortcut" resource.
 */
final class ShortcutResource extends ResourceComponent implements DownloadableResourceInterface, EvaluatedResourceInterface
{
    public function __construct(
        private readonly ResourceManager $resourceManager
    ) {
    }

    public static function getName(): string
    {
        return 'shortcut';
    }

    public static function supportsScore(): bool
    {
        return false;
    }

    public static function supportsAttempts(): bool
    {
        return false;
    }

    /** @param Shortcut $resource */
    public function download(AbstractResource $resource, FileBag $fileBag): void
    {
        // forward download to the target resource
        if ($resource->getTarget()) {
            $this->resourceManager->download([$resource->getTarget()], $fileBag);
        }
    }
}
