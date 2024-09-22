<?php

namespace Claroline\ThemeBundle\Controller;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ThemeBundle\Entity\UserPreferences;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/appearance/preferences')]
class UserPreferencesController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer
    ) {
        $this->authorization = $authorization;
    }

    #[Route(path: '', name: 'apiv2_theme_preference_update', methods: ['PUT'])]
    public function updateAction(Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $data = $this->decodeRequest($request);

        $usersPreferences = $this->om->getRepository(UserPreferences::class)->findOneBy(['user' => $this->tokenStorage->getToken()->getUser()]) ?? new UserPreferences();
        $usersPreferences->setUser($this->tokenStorage->getToken()->getUser());
        $this->serializer->deserialize($data, $usersPreferences);

        $this->om->persist($usersPreferences);
        $this->om->flush();

        return new JsonResponse(
            $this->serializer->serialize($usersPreferences)
        );
    }
}
