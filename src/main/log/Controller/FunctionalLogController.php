<?php

namespace Claroline\LogBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Security\PlatformRoles;
use Claroline\LogBundle\Entity\FunctionalLog;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/log/functional')]
class FunctionalLogController
{
    use PermissionCheckerTrait;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        private readonly Crud $crud
    ) {
        $this->authorization = $authorization;
    }

    #[Route(path: '', name: 'apiv2_logs_functional', methods: ['GET'])]
    public function listAction(
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        $this->checkPermission(PlatformRoles::ADMIN, null, [], true);

        $logs = $this->crud->search(FunctionalLog::class, $finderRequest, [SerializerInterface::SERIALIZE_LIST]);

        return $logs->toResponse();
    }

    #[Route(path: '/current', name: 'apiv2_logs_functional_list_current', methods: ['GET'])]
    public function listForCurrentUserAction(
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $user = $this->tokenStorage->getToken()?->getUser();
        $finderRequest->addFilter('doer', $user->getUuid());

        $logs = $this->crud->search(FunctionalLog::class, $finderRequest, [SerializerInterface::SERIALIZE_LIST]);

        return $logs->toResponse();
    }

    #[Route(path: '/user/{userId}', name: 'apiv2_logs_functional_list_user', methods: ['GET'])]
    public function listByUserAction(
        #[MapEntity(mapping: ['userId' => 'uuid'])]
        User $user,
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        $this->checkPermission('OPEN', $user, [], true);

        $finderRequest->addFilter('doer', $user->getUuid());

        $logs = $this->crud->search(FunctionalLog::class, $finderRequest, [SerializerInterface::SERIALIZE_LIST]);

        return $logs->toResponse();
    }

    #[Route(path: '/group/{groupId}', name: 'apiv2_logs_functional_list_group', methods: ['GET'])]
    public function listByGroupAction(
        #[MapEntity(mapping: ['groupId' => 'uuid'])]
        Group $group,
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        $this->checkPermission('OPEN', $group, [], true);

        $finderRequest->addFilter('doer.groups', $group->getUuid());

        $logs = $this->crud->search(FunctionalLog::class, $finderRequest, [SerializerInterface::SERIALIZE_LIST]);

        return $logs->toResponse();
    }
}
