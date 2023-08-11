<?php

namespace Claroline\FlashcardBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\User;
use Claroline\FlashcardBundle\Entity\Flashcard;
use Claroline\FlashcardBundle\Entity\FlashcardDeck;
use Claroline\FlashcardBundle\Entity\UserProgression;
use Claroline\FlashcardBundle\Serializer\UserProgressionSerializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * @Route("/flashcard_deck")
 */
class FlashcardDeckController extends AbstractCrudController
{
    private UserProgressionSerializer $userProgressionSerializer;

    public function __construct(UserProgressionSerializer $userProgressionSerializer)
    {
        $this->userProgressionSerializer = $userProgressionSerializer;
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
     * @EXT\ParamConverter("card", class="Claroline\FlashcardBundle\Entity\Flashcard")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function updateProgressionAction(Flashcard $card, User $user, Request $request): JsonResponse
    {
        $userProgression = $this->om->getRepository(UserProgression::class)->findOneBy([
            'user' => $user,
            'flashcard' => $card,
        ]);

        if (!$userProgression) {
            $userProgression = new UserProgression();
            $userProgression->setUser($user);
            $userProgression->setFlashcard($card);
        }

        $userProgression->setIsSuccessful( $request->get('isSuccessful') === "true" );
        $this->om->persist($userProgression);
        $this->om->flush();

        return new JsonResponse([
            "progression" => $this->userProgressionSerializer->serialize($userProgression),
        ]);
    }
}
