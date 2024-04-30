<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LinkBundle\Listener\Resource\Types;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\LinkBundle\Entity\Resource\Shortcut;

/**
 * Integrates the "Shortcut" resource.
 */
class ShortcutListener extends ResourceComponent
{
    public function __construct(
        private readonly SerializerProvider $serializer
    ) {
    }

    public static function getName(): string
    {
        return 'shortcut';
    }

    /** @var Shortcut $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        return [
            'shortcut' => $this->serializer->serialize($resource),
        ];
    }
}
