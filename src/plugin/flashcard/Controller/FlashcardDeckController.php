<?php

namespace Claroline\FlashcardBundle\Controller;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\Resource\ResourceEvaluationRepository;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
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
    /** @var ObjectManager */
    protected $om;
    private FlashcardManager $flashcardManager;
    private EvaluationManager $evaluationManager;
    private ResourceEvaluationManager $resourceEvalManager;
    private ResourceEvaluationRepository $resourceEvalRepo;

    public function __construct(
        ObjectManager             $om,
        FlashcardManager          $flashcardManager,
        EvaluationManager         $evaluationManager,
        ResourceEvaluationManager $resourceEvalManager
    ) {
        $this->om = $om;
        $this->flashcardManager = $flashcardManager;
        $this->evaluationManager = $evaluationManager;
        $this->resourceEvalManager = $resourceEvalManager;
        $this->resourceEvalRepo = $this->om->getRepository(ResourceEvaluation::class);
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
        $deck = $this->om->getRepository(FlashcardDeck::class)->findOneBy(['id' => $id]);
        $newDeckData = $this->decodeRequest($request);

        return new JsonResponse([
            'resetAttempts' => $this->flashcardManager->shouldResetAttempts($deck, $newDeckData),
        ]);
    }

    public function updateAction($id, Request $request, $class): JsonResponse
    {
        $deck = $this->om->getRepository(FlashcardDeck::class)->findOneBy(['id' => $id]);

        if ($this->flashcardManager->shouldResetAttempts($deck, $this->decodeRequest($request))) {
            $attempts = $this->resourceEvalRepo->findInProgress($deck->getResourceNode());
            foreach ($attempts as $attempt) {
                $this->om->remove($attempt);
            }
            $this->om->flush();
        }

        return parent::updateAction($id, $request, $class);
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
        $node = $card->getDeck()->getResourceNode();
        $cardsDrawnProgression = $this->evaluationManager->updateCardDrawnProgression($card, $user, $request->get('isSuccessful'));
        $resourceUserEvaluation = $this->evaluationManager->getResourceUserEvaluation($node, $user);

        $progression = [];
        foreach ($cardsDrawnProgression as $cardProgression) {
            $progression[] = $this->serializer->serialize($cardProgression);
        }

        $attempt = $this->resourceEvalRepo->findOneInProgress($node, $user);
        if (!$attempt) {
            $attempt = $this->resourceEvalRepo->findLast($node, $user);
        }

        return new JsonResponse([
            'attempt' => $this->serializer->serialize($attempt),
            'flashcardProgression' => $progression,
            'userEvaluation' => $this->serializer->serialize($resourceUserEvaluation, [SerializerInterface::SERIALIZE_MINIMAL]),
        ]);
    }

    /**
     * @Route("/{id}/attempt", name="apiv2_flashcard_deck_current_attempt", methods={"GET"})
     * @EXT\ParamConverter("deck", class="Claroline\FlashcardBundle\Entity\FlashcardDeck", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function getAttemptAction(FlashcardDeck $deck, ?User $user = null): JsonResponse
    {
        $node = $deck->getResourceNode();
        $attempt = $this->resourceEvalRepo->findOneInProgress($node, $user);

        if (!$attempt) {
            $attempt = $this->resourceEvalManager->createAttempt($node, $user, [
                'status' => AbstractEvaluation::STATUS_OPENED,
                'progression' => 0,
                'data' => [
                    'session' => 1,
                    'nextCardIndex' => 0,
                ]
            ]);
        }

        return new JsonResponse([
            'attempt' => $this->serializer->serialize($attempt),
            'flashcardProgression' => $this->flashcardManager->getAttemptCardsProgression($deck, $attempt, $user),
        ]);
    }
}
