<?php

namespace Claroline\LogBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Security\PlatformRoles;
use Claroline\LogBundle\Entity\MessageLog;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/log/message')]
class MessageLogController
{
    use PermissionCheckerTrait;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        private readonly Crud $crud
    ) {
        $this->authorization = $authorization;
    }

    #[Route(path: '', name: 'apiv2_logs_message', methods: ['GET'])]
    public function listAction(
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        $this->checkPermission(PlatformRoles::ADMIN, null, [], true);

        $logs = $this->crud->search(MessageLog::class, $finderRequest, [SerializerInterface::SERIALIZE_LIST]);

        return $logs->toResponse();
    }

    #[Route(path: '/current', name: 'apiv2_logs_message_list_current', methods: ['GET'])]
    public function listForCurrentUserAction(
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $user = $this->tokenStorage->getToken()?->getUser();
        $finderRequest->addFilter('doer', $user->getUuid());

        $logs = $this->crud->search(MessageLog::class, $finderRequest, [SerializerInterface::SERIALIZE_LIST]);

        return $logs->toResponse();
    }
}
