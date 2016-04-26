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
    private $sessionMgr;
    private $formHandler;
    private $checker;
    private $tokenStorage;
    private $serializer;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.flashcard.card_learning_manager"),
     *     "handler" = @DI\Inject("claroline.form_handler"),
     *     "checker" = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage" = @DI\Inject("security.token_storage"),
     *     "serializer" = @DI\Inject("serializer")
     * })
     *
     * @param CardLearningManager           $cardLearningMgr
     * @param FormHandler                   $handler
     * @param AuthorizationCheckerInterface $checker
     * @param TokenStorageInterface         $tokenStorage
     * @param $serializer
     */
    public function __construct(
        CardLearningManager $manager,
        FormHandler $handler,
        AuthorizationCheckerInterface $checker,
        TokenStorageInterface $tokenStorage,
        $serializer
    )
    {
        $this->manager = $manager;
        $this->formHandler = $handler;
        $this->checker = $checker;
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
    }


    /**
     * @EXT\Route(
     *     "/card_learning/all/deck/{deck}", 
     *     name="claroline_getall_card_learning"
     * )
     *
     * @param Deck $deck
     * @return JsonResponse
     */
    public function allCardLearning(Deck $deck)
    {
        // Must do something when user is not connected !
        $user = $this->tokenStorage->getToken()->getUser();

        $cardLearnings = $this->manager->allCardLearning($deck, $user);

        $context = new SerializationContext();
        $context->setGroups('api_flashcard_card');
        return new JsonResponse(json_decode(
            $this->serializer->serialize($cardLearnings, 'json', $context)
        ));
    }
}
