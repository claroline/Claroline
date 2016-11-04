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
use Claroline\FlashCardBundle\Manager\DeckManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @EXT\Route(requirements={"id"="\d+", "abilityId"="\d+"}, options={"expose"=true})
 * @EXT\Method("GET")
 */
class DeckController
{
    private $manager;
    private $formHandler;
    private $tokenStorage;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.flashcard.deck_manager"),
     *     "handler" = @DI\Inject("claroline.form_handler"),
     *     "checker" = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage" = @DI\Inject("security.token_storage"),
     *     "serializer" = @DI\Inject("serializer")
     * })
     *
     * @param DeckManager                   $manager
     * @param FormHandler                   $handler
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $checker
     */
    public function __construct(
        DeckManager $manager,
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
     * @EXT\Route("/{deck}", name="claroline_open_flashcard")
     * @EXT\Template
     *
     * @param Deck $deck
     *
     * @return array
     */
    public function deckAction(Deck $deck)
    {
        $this->assertCanOpen($deck);

        $canEdit = $this->checker->isGranted('EDIT', $deck);

        $user = $this->tokenStorage->getToken()->getUser();

        $theme = $deck->getUserPreference($user)->getTheme();

        return [
            '_resource' => $deck,
            '_canEdit' => $canEdit,
            '_theme' => $theme,
        ];
    }

    /**
     * @EXT\Route("/deck/get_all_themes", name="claroline_get_all_themes")
     * @EXT\Method("GET")
     *
     * @return array
     */
    public function getAllThemesAction()
    {
        $response = new JsonResponse();

        return $response->setData(Deck::getAllThemes());
    }

    /**
     * @EXT\Route(
     *     "/deck/edit/default_param/{deck}",
     *     name="claroline_edit_default_param"
     * )
     * @EXT\Method("POST")
     *
     * @param Request $request
     * @param Deck    $deck
     *
     * @return JsonResponse
     */
    public function editDefaultParamAction(Request $request, Deck $deck)
    {
        $newCardDay = $request->request->get('newCardDay', false);
        $theme = $request->request->get('theme', false);
        $response = new JsonResponse();

        $this->assertCanEdit($deck);

        if ($newCardDay && $newCardDay > 0 && $theme) {
            $deck->setNewCardDayDefault($newCardDay);
            $deck->setTheme($theme);

            $deck = $this->manager->create($deck);

            $context = new SerializationContext();
            $context->setGroups('api_flashcard_deck');
            $response->setData(json_decode(
                $this->serializer->serialize($deck, 'json', $context)
            ));
        } else {
            $response->setData('Field "newCardDay" or field "theme" is missing');
            $response->setStatusCode(422);
        }

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/deck/edit/user_param/{deck}",
     *     name="claroline_edit_user_param"
     * )
     * @EXT\Method("POST")
     *
     * @param Request $request
     * @param Deck    $deck
     *
     * @return JsonResponse
     */
    public function editUserParamAction(Request $request, Deck $deck)
    {
        $newCardDay = $request->request->get('newCardDay', false);
        $theme = $request->request->get('theme', false);
        $response = new JsonResponse();

        $this->assertCanOpen($deck);

        $user = $this->tokenStorage->getToken()->getUser();

        if ($newCardDay && $newCardDay > 0 && $theme) {
            $userPref = $deck->getUserPreference($user);
            $userPref->setNewCardDay($newCardDay);
            $userPref->setTheme($theme);

            $deck->setUserPreference($userPref);

            $deck = $this->manager->create($deck);

            $context = new SerializationContext();
            $context->setGroups('api_flashcard_deck');
            $response->setData(json_decode(
                $this->serializer->serialize($deck, 'json', $context)
            ));
        } else {
            $response->setData('Field "newCardDay" or field "theme" is missing');
            $response->setStatusCode(422);
        }

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/deck/{deck}/get_user_pref",
     *     name="claroline_get_user_pref"
     * )
     *
     * @param Deck $deck
     *
     * @return JsonResponse
     */
    public function getUserPreference(Deck $deck)
    {
        $response = new JsonResponse();

        $this->assertCanOpen($deck);

        $user = $this->tokenStorage->getToken()->getUser();

        $userPref = $deck->getUserPreference($user);

        $context = new SerializationContext();
        $context->setGroups('api_flashcard_user_pref');
        $response->setData(json_decode(
            $this->serializer->serialize($userPref, 'json', $context)
        ));

        return $response;
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

    private function assertCanEdit($obj)
    {
        if (!$this->checker->isGranted('EDIT', $obj)) {
            throw new AccessDeniedHttpException();
        }
    }
}
