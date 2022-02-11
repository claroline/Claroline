<?php

namespace Claroline\CoreBundle\API\Serializer\Resource\Types;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\Resource\File\LoadFileEvent;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mime\MimeTypes;
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

    public function __construct(
        RouterInterface $router,
        string $filesDir,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->router = $router;
        $this->filesDir = $filesDir;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getName()
    {
        return 'file';
    }

    /**
     * Serializes a File resource entity for the JSON api.
     *
     * @param File $file - the file to serialize
     *
     * @return array - the serialized representation of the file
     */
    public function serialize(File $file): array
    {
        $ext = pathinfo($file->getHashName(), PATHINFO_EXTENSION);
        if (empty($ext)) {
            $mimeTypeGuesser = new MimeTypes();
            $guessedExtension = $mimeTypeGuesser->getExtensions($file->getResourceNode()->getMimeType());
            if (!empty($guessedExtension)) {
                $ext = $guessedExtension[0];
            }
        }

        $fileName = TextNormalizer::toKey(str_replace('.'.$ext, '', $file->getResourceNode()->getName())).'.'.$ext;

        $serialized = [
            'id' => $file->getUuid(),
            'size' => $file->getSize(),
            'opening' => $file->getOpening(),
            'commentsActivated' => $file->getResourceNode()->isCommentsActivated(),
            'name' => $fileName, // the name of the file which will be used for file download
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
            new LoadFileEvent($file, $this->filesDir.DIRECTORY_SEPARATOR.$file->getHashName()),
            $this->generateEventName($file->getResourceNode(), 'load')
        );

        if ($fallBackEvent->isPopulated()) {
            $additionalFileData = $fallBackEvent->getData();
        }

        return array_merge($additionalFileData, $serialized);
    }

    public function deserialize($data, File $file, array $options = []): File
    {
        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $file);
        } else {
            $file->refreshUuid();
        }

        $this->sipe('size', 'setSize', $data, $file);
        $this->sipe('hashName', 'setHashName', $data, $file);
        $this->sipe('opening', 'setOpening', $data, $file);

        if (isset($data['commentsActivated']) && $file->getResourceNode()) {
            $resourceNode = $file->getResourceNode();
            $resourceNode->setCommentsActivated($data['commentsActivated']);
        }
        if ($file->getResourceNode()) {
            $dataEvent = new GenericDataEvent([
                'resourceNode' => $file->getResourceNode(),
                'data' => $data,
            ]);
            $this->eventDispatcher->dispatch($dataEvent, 'resource.file.deserialize');
        }

        return $file;
    }

    private function generateEventName(ResourceNode $node, $event): string
    {
        $mimeType = $node->getMimeType();
        $mimeElements = explode('/', $mimeType);
        $suffix = strtolower($mimeElements[0]);
        $eventName = strtolower(str_replace('/', '_', $suffix));
        $eventName = str_replace('"', '', $eventName);

        return 'file.'.$eventName.'.'.$event;
    }
}
