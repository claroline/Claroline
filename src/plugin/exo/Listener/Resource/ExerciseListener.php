<?php

namespace UJM\ExoBundle\Listener\Resource;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Component\Resource\EvaluatedResourceInterface;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Manager\AttemptManager;
use UJM\ExoBundle\Manager\ExerciseManager;
use UJM\ExoBundle\Repository\ExerciseRepository;

/**
 * Listens to resource events dispatched by the core.
 */
class ExerciseListener extends ResourceComponent implements EvaluatedResourceInterface
{
    private ExerciseRepository $repository;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly SerializerProvider $serializer,
        private readonly ExerciseManager $exerciseManager,
        private readonly PaperManager $paperManager,
        private readonly AttemptManager $attemptManager,
        private readonly ResourceEvaluationManager $resourceEvalManager,
        ObjectManager $om
    ) {
        $this->repository = $om->getRepository(Exercise::class);
    }

    public static function getName(): string
    {
        return 'ujm_exercise';
    }

    /** @var Exercise $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $canEdit = $this->authorization->isGranted('EDIT', $resource->getResourceNode());

        $options = [];
        if ($canEdit || $resource->hasStatistics()) {
            $options[] = Transfer::INCLUDE_SOLUTIONS;
        }

        // fetch additional user data
        $lastAttempt = null;
        $userEvaluation = null;
        if ($currentUser instanceof User) {
            $lastAttempt = $this->attemptManager->getLastPaper($resource, $currentUser);

            $userEvaluation = $this->serializer->serialize(
                $this->resourceEvalManager->getUserEvaluation($resource->getResourceNode(), $currentUser),
                [Options::SERIALIZE_MINIMAL]
            );
        }

        return [
            'resource' => $this->serializer->serialize($resource, $options),
            // user data
            'lastAttempt' => $lastAttempt ? $this->paperManager->serialize($lastAttempt) : null,
            'userEvaluation' => $userEvaluation,
        ];
    }

    /** @var Exercise $resource */
    public function update(AbstractResource $resource, array $data): ?array
    {
        // Invalidate unfinished papers
        $this->repository->invalidatePapers($resource);

        return [
            'resource' => $this->serializer->serialize($resource, [Transfer::INCLUDE_SOLUTIONS]),
        ];
    }

    /** @var Exercise $resource */
    public function delete(AbstractResource $resource, FileBag $fileBag, bool $softDelete = true): bool
    {
        // we cannot delete an evaluative quiz with results
        return $this->exerciseManager->isDeletable($resource);
    }
}
