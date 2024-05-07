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
use Claroline\AppBundle\Manager\PdfManager;
use Claroline\CoreBundle\Component\Resource\DownloadableResourceInterface;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Manager\Template\PlaceholderManager;

class TextListener extends ResourceComponent implements DownloadableResourceInterface
{
    public function __construct(
        private readonly SerializerProvider $serializer,
        private readonly PdfManager $pdfManager,
        private readonly PlaceholderManager $placeholderManager
    ) {
    }

    public static function getName(): string
    {
        return 'text';
    }

    /** @param Text $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        return [
            'resource' => $this->serializer->serialize($resource),
            'placeholders' => $this->placeholderManager->getAvailablePlaceholders(),
        ];
    }

    /** @param Text $resource */
    public function update(AbstractResource $resource, array $data): ?array
    {
        return [
            'resource' => $this->serializer->serialize($resource),
        ];
    }

    /** @param Text $resource */
    public function download(AbstractResource $resource): ?string
    {
        return $this->pdfManager->fromHtml(
            $this->placeholderManager->replacePlaceholders($resource->getContent() ?? '')
        );
    }
}
