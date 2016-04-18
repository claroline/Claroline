<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\FlashCardBundle\Controller;

use Claroline\CoreBundle\Form\Handler\FormHandler;
use Claroline\FlashCardBundle\Entity\CardLearning;
use Claroline\FlashCardBundle\Entity\Deck;
use Claroline\FlashCardBundle\Manager\CardLearningManager;
use Claroline\FlashCardBundle\Manager\CardManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @EXT\Route(requirements={"id"="\d+", "abilityId"="\d+"}, options={"expose"=true})
 * @EXT\Method("GET")
 */
class CardLearningController
{
    private $cardMgr;
    private $cardLearningMgr;
    private $formHandler;
    private $checker;
    private $tokenStorage;
    private $serializer;

    /**
     * @DI\InjectParams({
     *     "cardMgr" = @DI\Inject("claroline.flashcard.card_manager"),
     *     "cardLearningMgr" = @DI\Inject("claroline.flashcard.card_learning_manager"),
     *     "handler" = @DI\Inject("claroline.form_handler"),
     *     "checker" = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage" = @DI\Inject("security.token_storage"),
     *     "serializer" = @DI\Inject("serializer")
     * })
     *
     * @param CardManager               $cardMgr
     * @param CardLearningManager               $cardLearningMgr
     * @param FormHandler                   $handler
     * @param AuthorizationCheckerInterface $checker
     * @param TokenStorageInterface $tokenStorage
     * @param $serializer
     */
    public function __construct(
        CardManager $cardMgr,
        CardLearningManager $cardLearningMgr,
        FormHandler $handler,
        AuthorizationCheckerInterface $checker,
        TokenStorageInterface $tokenStorage,
        $serializer
    )
    {
        $this->cardMgr = $cardMgr;
        $this->cardLearningMgr = $cardLearningMgr;
        $this->formHandler = $handler;
        $this->checker = $checker;
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
    }

    /**
     * @EXT\Route("/card/new_to_learn/deck/{deck}", name="claroline_new_card_to_learn")
     *
     * @param Deck $deck
     * @return JsonResponse
     */
    public function newCardToLearnAction(Deck $deck)
    {
        // Must do something when user is not connected !
        $user = $this->tokenStorage->getToken()->getUser();

        $cards = $this->cardMgr->getNewCardToLearn($deck, $user);

        $cardLearnings = [];
        foreach($cards as $card) {
            $cardLearning = new CardLearning();
            $cardLearning->setCard($card);
            $cardLearnings[] = $cardLearning;
        }

        $context = new SerializationContext();
        $context->setGroups('api_flashcard_card');
        return new JsonResponse(json_decode(
            $this->serializer->serialize($cardLearnings, 'json', $context)
        ));
    }

    /**
     * @EXT\Route("/card/card_to_review/deck/{deck}", name="claroline_card_to_review")
     *
     * @param Deck $deck
     * @return JsonResponse
     */
    public function cardToReviewAction(Deck $deck)
    {
        // Must do something when user is not connected !
        $user = $this->tokenStorage->getToken()->getUser();
        $date = new \DateTime();

        $cardLearnings = $this->cardLearningMgr->getCardToReview($deck, $user, $date);

        $context = new SerializationContext();
        $context->setGroups('api_flashcard_card');
        return new JsonResponse(json_decode(
            $this->serializer->serialize($cardLearnings, 'json', $context)
        ));
    }
}
