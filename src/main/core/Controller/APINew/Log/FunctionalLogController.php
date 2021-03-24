<?php

namespace Claroline\CoreBundle\Controller\APINew\Log;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\CoreBundle\Entity\Log\FunctionalLog;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/log/functional")
 */
class FunctionalLogController extends AbstractSecurityController
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
     * @Route("", name="apiv2_logs_functional", methods={"GET"})
     */
    public function listAction(Request $request): JsonResponse
    {
        $this->canOpenAdminTool('dashboard');

        return new JsonResponse($this->finderProvider->search(
            FunctionalLog::class,
            $request->query->all(),
            []
        ));
    }

    /**
     * @Route("/list/current", name="apiv2_logs_functional_list_current", methods={"GET"})
     */
    public function userLogFunctionalAction(): JsonResponse
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $user = $this->tokenStorage->getToken()->getUser();

        $query['hiddenFilters'] = [
            'user' => $user->getUuid(),
        ];

        return new JsonResponse($this->finderProvider->search(
            FunctionalLog::class,
            $query,
            []
        ));
    }
}
