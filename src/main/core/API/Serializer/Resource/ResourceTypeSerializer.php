<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\CoreBundle\Component\Resource\AdapterInterface;
use Claroline\CoreBundle\Component\Resource\DownloadableResourceInterface;
use Claroline\CoreBundle\Component\Resource\FileAdapterInterface;
use Claroline\CoreBundle\Component\Resource\ResourceProvider;
use Claroline\CoreBundle\Component\Resource\UrlAdapterInterface;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\EvaluationBundle\Component\Resource\EvaluatedResourceInterface;

class ResourceTypeSerializer
{
    public function __construct(
        private readonly ResourceProvider $resourceProvider
    ) {
    }

    public function getClass(): string
    {
        return ResourceType::class;
    }

    public function getName(): string
    {
        return 'resource_type';
    }

    /**
     * Serializes a ResourceType entity for the JSON api.
     */
    public function serialize(ResourceType $resourceType): array
    {
        $resourceHandler = $this->resourceProvider->getComponent($resourceType->getName());

        $download = $resourceHandler instanceof DownloadableResourceInterface;
        $score = false;
        $attempts = false;
        $evaluation = false;
        if ($resourceHandler instanceof EvaluatedResourceInterface) {
            $evaluation = true;
            $score = $resourceHandler::supportsScore();
            $attempts = $resourceHandler::supportsAttempts();
        }

        $adapters = [];
        $requireAdapter = false;
        if ($resourceHandler instanceof AdapterInterface) {
            $requireAdapter = $resourceHandler->requireAdapter();
            if ($resourceHandler instanceof FileAdapterInterface) {
                $adapters[] = 'file';
            }

            if ($resourceHandler instanceof UrlAdapterInterface) {
                $adapters[] = 'url';
            }
        }

        return [
            'id' => $resourceType->getId(),
            'name' => $resourceType->getName(),
            'class' => $resourceType->getClass(),
            'enabled' => $resourceType->isEnabled(),
            'evaluation' => $evaluation,
            'score' => $score,
            'attempts' => $attempts,
            'downloadable' => $download,
            'adapters' => $adapters,
            'requireAdapter' => $requireAdapter,
        ];
    }
}
