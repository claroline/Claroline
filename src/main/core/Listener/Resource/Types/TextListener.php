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

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Manager\Template\PlaceholderManager;

class TextListener extends ResourceComponent
{
    public function __construct(
        private readonly SerializerProvider $serializer,
        private readonly PlaceholderManager $placeholderManager
    ) {
    }

    public static function getName(): string
    {
        return 'text';
    }

    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        return [
            'text' => $this->serializer->serialize($resource),
            'placeholders' => $this->placeholderManager->getAvailablePlaceholders(),
        ];
    }

    public function update(AbstractResource $resource, array $data): ?array
    {
        return [
            'resource' => $this->serializer->serialize($resource),
        ];
    }
}
