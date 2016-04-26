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
use Claroline\FlashCardBundle\Entity\Card;
use Claroline\FlashCardBundle\Entity\Session;
use Claroline\FlashCardBundle\Manager\CardLearningManager;
use Claroline\FlashCardBundle\Manager\CardManager;
use Claroline\FlashCardBundle\Manager\SessionManager;
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
class CardController
{
    private $cardMgr;
    private $cardLearningMgr;
    private $sessionMgr;
    private $formHandler;
    private $checker;
    private $tokenStorage;
    private $serializer;

    /**
     * @DI\InjectParams({
     *     "cardMgr" = @DI\Inject("claroline.flashcard.card_manager"),
     *     "cardLearningMgr" = @DI\Inject("claroline.flashcard.card_learning_manager"),
     *     "sessionMgr" = @DI\Inject("claroline.flashcard.session_manager"),
     *     "handler" = @DI\Inject("claroline.form_handler"),
     *     "checker" = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage" = @DI\Inject("security.token_storage"),
     *     "serializer" = @DI\Inject("serializer")
     * })
     *
     * @param CardManager                   $cardMgr
     * @param CardLearningManager           $cardLearningMgr
     * @param SessionManager                $sessionMgr
     * @param FormHandler                   $handler
     * @param AuthorizationCheckerInterface $checker
     * @param TokenStorageInterface         $tokenStorage
     * @param $serializer
     */
    public function __construct(
        CardManager $cardMgr,
        CardLearningManager $cardLearningMgr,
        SessionManager $sessionMgr,
        FormHandler $handler,
        AuthorizationCheckerInterface $checker,
        TokenStorageInterface $tokenStorage,
        $serializer
    )
    {
        $this->cardMgr = $cardMgr;
        $this->cardLearningMgr = $cardLearningMgr;
        $this->sessionMgr = $sessionMgr;
        $this->formHandler = $handler;
        $this->checker = $checker;
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
    }

    /**
     * @EXT\Route(
     *     "/card/new_card_to_learn/deck/{deck}", 
     *     name="claroline_new_card_to_learn"
     * )
     *
     * @param Deck $deck
     * @return JsonResponse
     */
    public function newCardToLearnAction(Deck $deck)
    {
        // Must do something when user is not connected !
        $user = $this->tokenStorage->getToken()->getUser();

        $cards = $this->cardMgr->getNewCardToLearn($deck, $user);

        $context = new SerializationContext();
        $context->setGroups('api_flashcard_card');
        return new JsonResponse(json_decode(
            $this->serializer->serialize($cards, 'json', $context)
        ));
    }

    /**
     * @EXT\Route(
     *     "/card/card_to_review/deck/{deck}", 
     *     name="claroline_card_to_review"
     * )
     *
     * @param Deck $deck
     * @return JsonResponse
     */
    public function cardToReviewAction(Deck $deck)
    {
        // Must do something when user is not connected !
        $user = $this->tokenStorage->getToken()->getUser();
        $date = new \DateTime();

        $cards = $this->cardMgr->getCardToReview($deck, $user, $date);

        $context = new SerializationContext();
        $context->setGroups('api_flashcard_card');
        return new JsonResponse(json_decode(
            $this->serializer->serialize($cards, 'json', $context)
        ));
    }

    /**
     * @EXT\Route(
     *     "/study_card/deck/{deck}/session/{sessionId}/card/{card}/result/{result}",
     *     name="claroline_study_card"
     * )
     *
     * @param Deck $deck
     * @param int $sessionId
     * @param Card $card
     * @param $result
     * @return JsonResponse
     */
    public function studyCardAction(Deck $deck, $sessionId, Card $card, $result)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $cardLearning = $this->cardLearningMgr->getCardLearning($card, $user);

        $isNewCard = $cardLearning == null;

        if($isNewCard) {
            $cardLearning = new cardLearning();
            $cardLearning->setCard($card);
            $cardLearning->setUser($user);
        }

        $cardLearning->study($result);

        $this->cardLearningMgr->save($cardLearning);

        // Save the session
        if($sessionId > 0) {
            $session = $this->sessionMgr->get($sessionId);
        } else {
            $session = new Session();
            $session->setDeck($deck);
            $session->setUser($user);
        }

        if($isNewCard) {
            $session->addNewCard($card);
        } else {
            $session->addOldCard($card);
        }

        $now = new \DateTime();
        $interval = $now->getTimestamp() - $session->getDate()->getTimestamp();
        $session->setDuration($session->getDuration() + $interval);

        $session = $this->sessionMgr->save($session);

        return new JsonResponse($session->getId());
    }

    /**
     * @EXT\Route(
     *     "/card/{card}/reset",
     *     name="claroline_reset_card"
     * )
     *
     * @param Card $card
     * @return JsonResponse
     */
    public function resetCardAction(Card $card)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $cardLearning = $this->cardLearningMgr->getCardLearning($card, $user);

        $this->cardLearningMgr->delete($cardLearning);

        return new JsonResponse($cardLearning->getId());
    }

    /**
     * @EXT\Route(
     *     "/card/{card}/suspend/{suspend}",
     *     name="claroline_suspend_card"
     * )
     *
     * @param Card $card
     * @param $suspend
     * @return JsonResponse
     */
    public function suspendCardAction(Card $card, $suspend)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $cardLearning = $this->cardLearningMgr->getCardLearning($card, $user);

        $cardLearning->setPainfull($suspend);

        $this->cardLearningMgr->save($cardLearning);

        return new JsonResponse($card->getId());
    }
}
