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
use Claroline\FlashCardBundle\Entity\Deck;
use Claroline\FlashCardBundle\Entity\Session;
use Claroline\FlashCardBundle\Manager\SessionManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @EXT\Route(requirements={"id"="\d+", "abilityId"="\d+"}, options={"expose"=true})
 * @EXT\Method("GET")
 */
class SessionController
{
    private $manager;
    private $formHandler;
    private $checker;
    private $tokenStorage;
    private $serializer;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.flashcard.session_manager"),
     *     "handler" = @DI\Inject("claroline.form_handler"),
     *     "checker" = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage" = @DI\Inject("security.token_storage"),
     *     "serializer" = @DI\Inject("serializer")
     * })
     *
     * @param SessionManager                $manager
     * @param FormHandler                   $handler
     * @param AuthorizationCheckerInterface $checker
     */
    public function __construct(
        SessionManager $manager,
        FormHandler $handler,
        AuthorizationCheckerInterface $checker,
        TokenStorageInterface $tokenStorage,
        $serializer
    ) {
        $this->manager = $manager;
        $this->formHandler = $handler;
        $this->checker = $checker;
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
    }

    /**
     * @EXT\Route(
     *     "/session/create/deck/{deck}",
     *     name="claroline_create_session"
     * )
     *
     * @param Deck $deck
     *
     * @return JsonResponse
     */
    public function createSessionAction(Deck $deck)
    {
        $this->assertCanOpen($deck);

        $user = $this->tokenStorage->getToken()->getUser();

        $session = new Session();
        $session->setDeck($deck);
        $session->setUser($user);

        $session = $this->manager->save($session);

        $context = new SerializationContext();
        $context->setGroups('api_flashcard_session');

        return new JsonResponse(json_decode(
            $this->serializer->serialize($session, 'json', $context)
        ));
    }

    private function assertCanOpen($obj)
    {
        if (!$this->checker->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedHttpException();
        }
        if (!$this->checker->isGranted('OPEN', $obj)) {
            throw new AccessDeniedHttpException();
        }
    }
}
