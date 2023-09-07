<?php

namespace Claroline\YouTubeBundle\Subscriber\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\YouTubeBundle\Entity\Video;
use Claroline\YouTubeBundle\Manager\EvaluationManager;
use Claroline\YouTubeBundle\Manager\YouTubeManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

class VideoSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var Environment */
    private $templating;
    /** @var SerializerProvider */
    private $serializer;
    /** @var EvaluationManager */
    private $evaluationManager;
    /** @var FileManager */
    private $fileManager;
    /** @var YouTubeManager */
    private $youtubeManager;
    /** @var Crud */
    private $crud;
    /** @var ObjectManager */
    private $om;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        Environment $templating,
        SerializerProvider $serializer,
        EvaluationManager $evaluationManager,
        FileManager $fileManager,
        YouTubeManager $youtubeManager,
        Crud $crud,
        ObjectManager $om
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->templating = $templating;
        $this->serializer = $serializer;
        $this->evaluationManager = $evaluationManager;
        $this->fileManager = $fileManager;
        $this->youtubeManager = $youtubeManager;
        $this->crud = $crud;
        $this->om = $om;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'resource.youtube_video.load' => 'onLoad',
            Crud::getEventName('create', 'post', Video::class) => 'onCreate',
        ];
    }

    public function onLoad(LoadResourceEvent $event): void
    {
        /** @var Video $video */
        $video = $event->getResource();
        $user = $this->tokenStorage->getToken()->getUser();

        $event->setData([
            'video' => $this->serializer->serialize($video),
            'userEvaluation' => $user instanceof User ? $this->serializer->serialize(
                $this->evaluationManager->getResourceUserEvaluation($video->getResourceNode(), $user),
                [SerializerInterface::SERIALIZE_MINIMAL]
            ) : null,
            'url' => $video->getUrl(),
        ]);
        $event->stopPropagation();
    }

    public function onCreate(CreateEvent $event): void
    {
        $video = $event->getObject();
        $resourceNode = $video->getResourceNode();

        if (!$resourceNode->getThumbnail()) {
            $uploadedFile = $this->youtubeManager->getTemporaryThumbnailFile($video->getUrl());

            if ($uploadedFile) {
                $publicFile = $this->crud->create(PublicFile::class, [], ['file' => $uploadedFile]);

                $resourceNode->setThumbnail($publicFile->getUrl());
                $this->om->persist($resourceNode);

                $this->fileManager->linkFile(ResourceNode::class, $resourceNode->getUuid(), $publicFile->getUrl());

                $this->om->flush();
            }
        }
    }
}
