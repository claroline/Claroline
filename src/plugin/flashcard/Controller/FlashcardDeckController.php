<?php

namespace Claroline\FlashcardBundle\Controller;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\User;
use Claroline\FlashcardBundle\Entity\Flashcard;
use Claroline\FlashcardBundle\Entity\FlashcardDeck;
use Claroline\FlashcardBundle\Manager\EvaluationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/flashcard_deck")
 */
class FlashcardDeckController extends AbstractCrudController
{
    private EvaluationManager $evaluationManager;

    public function __construct(
        EvaluationManager $evaluationManager
    ) {
        $this->evaluationManager = $evaluationManager;
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
     * Update card progression for an user.
     *
     * @Route("/flashcard/{id}/progression", name="apiv2_flashcard_progression_update", methods={"PUT"})
     *
     * @EXT\ParamConverter("card", class="Claroline\FlashcardBundle\Entity\Flashcard", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function updateProgressionAction(Flashcard $card, User $user, Request $request): JsonResponse
    {
        $cardDrawnProgression = $this->evaluationManager->updateCardDrawnProgression($card, $user, $request->get('isSuccessful'));

        $resourceUserEvaluation = $this->evaluationManager->getResourceUserEvaluation($card->getDeck()->getResourceNode(), $user);

        return new JsonResponse([
            'progression' => $this->serializer->serialize($cardDrawnProgression),
            'userEvaluation' => $this->serializer->serialize($resourceUserEvaluation, [SerializerInterface::SERIALIZE_MINIMAL]),
        ]);
    }
}
