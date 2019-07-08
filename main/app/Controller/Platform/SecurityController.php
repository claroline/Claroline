<?php

namespace Claroline\AppBundle\Controller\Platform;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SecurityController
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var SerializerProvider */
    private $serializer;

    /** @var UserManager */
    private $manager;

    /**
     * SecurityController constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage" = @DI\Inject("security.token_storage"),
     *     "serializer"   = @DI\Inject("claroline.api.serializer"),
     *     "manager"      = @DI\Inject("claroline.manager.user_manager")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     * @param SerializerProvider    $serializer
     * @param UserManager           $manager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        SerializerProvider $serializer,
        UserManager $manager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
        $this->manager = $manager;
    }

    /**
     * Logs a user into the platform (all of the security stuffs are done by symfony internals).
     *
     * @EXT\Route("/login", name="claro_security_login")
     * @EXT\Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        // retrieve logged user
        $user = $this->tokenStorage->getToken()->getUser();

        // switch to it's defined locale
        $request->setLocale($user->getLocale());

        $this->manager->logUser($user);

        return new JsonResponse([
            'user' => $this->serializer->serialize($user),
            'messages' => [],
        ]);
    }
}
