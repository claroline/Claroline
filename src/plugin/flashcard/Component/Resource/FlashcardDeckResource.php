<?php

namespace Claroline\FlashcardBundle\Component\Resource;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Component\Resource\EvaluatedResourceInterface;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceAttempt;
use Claroline\EvaluationBundle\Repository\UserEvaluation\ResourceAttemptRepository;
use Claroline\FlashcardBundle\Entity\FlashcardDeck;
use Claroline\FlashcardBundle\Manager\EvaluationManager;
use Claroline\FlashcardBundle\Manager\FlashcardManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class FlashcardDeckResource extends ResourceComponent implements EvaluatedResourceInterface
{
    private ResourceAttemptRepository $resourceEvalRepo;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly FlashcardManager $flashcardManager,
        private readonly EvaluationManager $evaluationManager
    ) {
        $this->resourceEvalRepo = $this->om->getRepository(ResourceAttempt::class);
    }

    public static function getName(): string
    {
        return 'flashcard';
    }

    public static function supportsScore(): bool
    {
        return false;
    }

    public static function supportsAttempts(): bool
    {
        return true;
    }

    /** @param FlashcardDeck $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        $evaluation = null;
        $attempt = null;
        $flashcardProgression = null;

        if ($user instanceof User) {
            $evaluation = $this->evaluationManager->getResourceUserEvaluation($resource->getResourceNode(), $user);
            $attempt = $this->resourceEvalRepo->findOneInProgress($resource->getResourceNode(), $user);
            $attempt = $this->flashcardManager->calculateSession($attempt, $resource, $user);
            $flashcardProgression = $attempt ? $attempt->getData()['cards'] ?? [] : [];
        }

        return [
            'resource' => $this->serializer->serialize($resource),
            'attempt' => $attempt ? $this->serializer->serialize($attempt) : null,
            'userEvaluation' => $evaluation ? $this->serializer->serialize($evaluation, [SerializerInterface::SERIALIZE_MINIMAL]) : null,
            'flashcardProgression' => $flashcardProgression,
        ];
    }

    /** @param FlashcardDeck $resource */
    public function update(AbstractResource $resource, array $data, array $previousData): ?array
    {
        return [
            'resource' => $this->serializer->serialize($resource),
        ];
    }
}
