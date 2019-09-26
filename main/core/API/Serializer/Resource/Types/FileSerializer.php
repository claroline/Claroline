<?php

namespace Claroline\CoreBundle\API\Serializer\Resource\Types;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\Resource\File\LoadFileEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class FileSerializer
{
    use SerializerTrait;

    /** @var RouterInterface */
    private $router;

    private $filesDir;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * ResourceNodeManager constructor.
     *
     * @param RouterInterface          $router
     * @param string                   $filesDir
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        RouterInterface $router,
        $filesDir,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->router = $router;
        $this->filesDir = $filesDir;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Serializes a File resource entity for the JSON api.
     *
     * @param File $file - the file to serialize
     *
     * @return array - the serialized representation of the file
     */
    public function serialize(File $file)
    {
        $options = [
            'id' => $file->getId(),
            'size' => $file->getSize(),
            'autoDownload' => $file->getAutoDownload(),
            'commentsActivated' => $file->getResourceNode()->isCommentsActivated(),
            'hashName' => $file->getHashName(),

            // We generate URL here because the stream API endpoint uses ResourceNode ID,
            // but the new api only contains the ResourceNode UUID.

            // NB : This will no longer be required when the stream API will use UUIDs
            'url' => $this->router->generate('claro_file_get_media', [
                'node' => $file->getResourceNode()->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
        ];

        $additionalFileData = [];

        $fallBackEvent = $this->eventDispatcher->dispatch(
            $this->generateEventName($file->getResourceNode(), 'load'),
            new LoadFileEvent($file, $this->filesDir.DIRECTORY_SEPARATOR.$file->getHashName())
        );

        if ($fallBackEvent->isPopulated()) {
            $additionalFileData = $fallBackEvent->getData();
        }

        return array_merge($additionalFileData, $options);
    }

    public function deserialize($data, File $file, array $options = [])
    {
        $this->sipe('size', 'setSize', $data, $file);
        $this->sipe('hashName', 'setHashName', $data, $file);
        $this->sipe('autoDownload', 'setAutoDownload', $data, $file);

        if (isset($data['commentsActivated']) && $file->getResourceNode()) {
            $resourceNode = $file->getResourceNode();
            $resourceNode->setCommentsActivated($data['commentsActivated']);
        }
        if ($file->getResourceNode()) {
            $dataEvent = new GenericDataEvent([
                'resourceNode' => $file->getResourceNode(),
                'data' => $data,
            ]);
            $this->eventDispatcher->dispatch('resource.file.deserialize', $dataEvent);
        }
    }

    private function generateEventName(ResourceNode $node, $event)
    {
        $mimeType = $node->getMimeType();
        $mimeElements = explode('/', $mimeType);
        $suffix = strtolower($mimeElements[0]);
        $eventName = strtolower(str_replace('/', '_', $suffix));
        $eventName = str_replace('"', '', $eventName);

        return 'file.'.$eventName.'.'.$event;
    }
}
