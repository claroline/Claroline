<?php

namespace Claroline\FlashcardBundle\Controller;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\EvaluationBundle\Repository\ResourceAttemptRepository;
use Claroline\FlashcardBundle\Entity\Flashcard;
use Claroline\FlashcardBundle\Entity\FlashcardDeck;
use Claroline\FlashcardBundle\Manager\EvaluationManager;
use Claroline\FlashcardBundle\Manager\FlashcardManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/flashcard_deck")
 */
class FlashcardDeckController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    private ResourceAttemptRepository $resourceEvalRepo;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly FlashcardManager $flashcardManager,
        private readonly EvaluationManager $evaluationManager,
    ) {
        $this->authorization = $authorization;
        $this->resourceEvalRepo = $om->getRepository(ResourceEvaluation::class);
    }

    /**
     * Check if deck modification should reset attempts.
     *
     * @Route("/{id}/check", name="apiv2_flashcard_deck_update_check", methods={"PUT"})
     * @EXT\ParamConverter("card", class="Claroline\FlashcardBundle\Entity\FlashcardDeck", options={"mapping": {"id": "uuid"}})
     */
    public function checkAction(FlashcardDeck $flashcardDeck, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $flashcardDeck);

        $oldDeckData = $this->serializer->serialize($flashcardDeck);
        $newDeckData = $this->decodeRequest($request);

        return new JsonResponse([
            'resetAttempts' => $this->flashcardManager->shouldResetAttempts($oldDeckData, $newDeckData),
        ]);
    }

    /**
     * Update card progression for a user.
     *
     * @Route("/flashcard/{id}/progression", name="apiv2_flashcard_progression_update", methods={"PUT"})
     *
     * @EXT\ParamConverter("card", class="Claroline\FlashcardBundle\Entity\Flashcard", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function updateProgressionAction(Flashcard $card, User $user, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $card->getDeck());

        $deck = $card->getDeck();
        $node = $deck->getResourceNode();
        $resourceUserEvaluation = $this->evaluationManager->getResourceUserEvaluation($node, $user);

        $this->evaluationManager->updateCardDrawnProgression($card, $user, $request->get('isSuccessful'));

        $attempt = $this->resourceEvalRepo->findOneInProgress($node, $user);
        $attempt = $this->flashcardManager->calculateSession($attempt, $deck, $user);

        return new JsonResponse([
            'attempt' => $this->serializer->serialize($attempt),
            'userEvaluation' => $this->serializer->serialize($resourceUserEvaluation, [SerializerInterface::SERIALIZE_MINIMAL]),
        ]);
    }

    /**
     * @Route("/{id}/attempt", name="apiv2_flashcard_deck_current_attempt", methods={"GET"})
     *
     * @EXT\ParamConverter("deck", class="Claroline\FlashcardBundle\Entity\FlashcardDeck", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function getAttemptAction(FlashcardDeck $deck, User $user): JsonResponse
    {
        $this->checkPermission('OPEN', $deck);

        $attempt = $this->resourceEvalRepo->findOneInProgress($deck->getResourceNode(), $user);
        $attempt = $this->flashcardManager->calculateSession($attempt, $deck, $user);

        return new JsonResponse($this->serializer->serialize($attempt));
    }
}
