<?php

namespace Claroline\LogBundle\Controller;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\LogBundle\Entity\SecurityLog;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/log/security")
 */
class SecurityLogController extends AbstractSecurityController
{
    private $finderProvider;
    private $authorization;
    private $tokenStorage;

    public function __construct(
        FinderProvider $finderProvider,
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage
    ) {
        $this->finderProvider = $finderProvider;
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Route("", name="apiv2_logs_security", methods={"GET"})
     */
    public function listAction(Request $request): JsonResponse
    {
        $this->canOpenAdminTool('logs');

        return new JsonResponse(
            $this->finderProvider->search(SecurityLog::class, $request->query->all())
        );
    }

    /**
     * @Route("/list/current", name="apiv2_logs_security_list_current", methods={"GET"})
     */
    public function userLogSecurityAction(Request $request): JsonResponse
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
            $this->finderProvider->search(SecurityLog::class, $query)
        );
    }
}
