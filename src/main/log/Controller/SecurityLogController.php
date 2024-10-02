<?php

namespace Claroline\LogBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\LogBundle\Entity\SecurityLog;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(path: '/log/security')]
class SecurityLogController extends AbstractSecurityController
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly Crud $crud
    ) {
    }

    #[Route(path: '', name: 'apiv2_logs_security', methods: ['GET'])]
    public function listAction(
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->canOpenAdminTool('logs');

        $logs = $this->crud->search(SecurityLog::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $logs->toResponse();
    }

    #[Route(path: '/current', name: 'apiv2_logs_security_list_current', methods: ['GET'])]
    public function listForCurrentUserAction(
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $user = $this->tokenStorage->getToken()?->getUser();
        $finderQuery->addFilter('doer', $user->getUuid());

        $logs = $this->crud->search(SecurityLog::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $logs->toResponse();
    }
}
