<?php

namespace Claroline\LogBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Security\PlatformRoles;
use Claroline\LogBundle\Entity\SecurityLog;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(path: '/log/security')]
class SecurityLogController
{
    use PermissionCheckerTrait;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        private readonly Crud $crud
    ) {
        $this->authorization = $authorization;
    }

    #[Route(path: '', name: 'apiv2_logs_security', methods: ['GET'])]
    public function listAction(
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        $this->checkPermission(PlatformRoles::ADMIN, null, [], true);

        $logs = $this->crud->search(SecurityLog::class, $finderRequest, [SerializerInterface::SERIALIZE_LIST]);

        return $logs->toResponse();
    }

    #[Route(path: '/current', name: 'apiv2_logs_security_list_current', methods: ['GET'])]
    public function listForCurrentUserAction(
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $user = $this->tokenStorage->getToken()?->getUser();
        $finderRequest->addFilter('target', $user->getUuid());

        $logs = $this->crud->search(SecurityLog::class, $finderRequest, [SerializerInterface::SERIALIZE_LIST]);

        return $logs->toResponse();
    }

    #[Route(path: '/user/{userId}', name: 'apiv2_logs_security_list_user', methods: ['GET'])]
    public function listByUserAction(
        #[MapEntity(mapping: ['userId' => 'uuid'])]
        User $user,
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        $this->checkPermission('EDIT', $user, [], true);

        $finderRequest->addFilter('target', $user->getUuid());

        $logs = $this->crud->search(SecurityLog::class, $finderRequest, [SerializerInterface::SERIALIZE_LIST]);

        return $logs->toResponse();
    }
}
