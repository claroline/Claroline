<?php

namespace Claroline\LogBundle\Controller;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\LogBundle\Entity\MessageLog;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(path: '/log/message')]
class MessageLogController extends AbstractSecurityController
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly FinderProvider $finder
    ) {
    }

    #[Route(path: '', name: 'apiv2_logs_message', methods: ['GET'])]
    public function listAction(Request $request): JsonResponse
    {
        $this->canOpenAdminTool('logs');

        return new JsonResponse(
            $this->finder->search(MessageLog::class, $request->query->all())
        );
    }

    #[Route(path: '/current', name: 'apiv2_logs_message_list_current', methods: ['GET'])]
    public function listForCurrentUserAction(Request $request): JsonResponse
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $user = $this->tokenStorage->getToken()->getUser();

        $query = $request->query->all();
        $query['hiddenFilters'] = [
            'doer' => $user->getUuid(),
        ];

        return new JsonResponse(
            $this->finder->search(MessageLog::class, $query)
        );
    }
}
