<?php

namespace Claroline\FlashcardBundle\Controller;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Repository\ResourceAttemptRepository;
use Claroline\FlashcardBundle\Entity\Flashcard;
use Claroline\FlashcardBundle\Entity\FlashcardDeck;
use Claroline\FlashcardBundle\Manager\EvaluationManager;
use Claroline\FlashcardBundle\Manager\FlashcardManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/flashcard_deck")
 */
class FlashcardDeckController extends AbstractCrudController
{
    private FlashcardManager $flashcardManager;
    private EvaluationManager $evaluationManager;
    private ResourceAttemptRepository $resourceEvalRepo;

    public function __construct(
        ObjectManager $om,
        FlashcardManager $flashcardManager,
        EvaluationManager $evaluationManager,
    ) {
        $this->flashcardManager = $flashcardManager;
        $this->evaluationManager = $evaluationManager;
        $this->resourceEvalRepo = $om->getRepository(ResourceEvaluation::class);
    }

    public function getName(): string
    {
        return 'flashcard_deck';
    }

    public function getClass(): string
    {
        return FlashcardDeck::class;
    }

    /**
     * Check if deck modification should reset attempts.
     *
     * @Route("/{id}/check", name="apiv2_flashcard_deck_update_check", methods={"PUT"})
     */
    public function checkAction($id, Request $request): JsonResponse
    {
        $deck = $this->om->getRepository(FlashcardDeck::class)->findOneBy(['uuid' => $id]);
        $oldDeckData = $this->serializer->serialize($deck);
        $newDeckData = $this->decodeRequest($request);

        return new JsonResponse([
            'resetAttempts' => $this->flashcardManager->shouldResetAttempts($oldDeckData, $newDeckData),
        ]);
    }

    /**
     * Update card progression for an user.
     *
     * @Route("/flashcard/{id}/progression", name="apiv2_flashcard_progression_update", methods={"PUT"})
     *
     * @EXT\ParamConverter("card", class="Claroline\FlashcardBundle\Entity\Flashcard", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function updateProgressionAction(Flashcard $card, User $user, Request $request): JsonResponse
    {
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
    public function getAttemptAction(FlashcardDeck $deck, User $user = null): JsonResponse
    {
        $attempt = $this->resourceEvalRepo->findOneInProgress($deck->getResourceNode(), $user);
        $attempt = $this->flashcardManager->calculateSession($attempt, $deck, $user);

        return new JsonResponse([
            'attempt' => $this->serializer->serialize($attempt),
        ]);
    }
}
