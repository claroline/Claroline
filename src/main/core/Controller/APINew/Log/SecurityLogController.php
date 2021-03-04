<?php

namespace Claroline\CoreBundle\Controller\APINew\Log;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\CoreBundle\Entity\Log\SecurityLog;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/log/security")
 */
class SecurityLogController extends AbstractSecurityController
{
    private $finderProvider;

    public function __construct(FinderProvider $finderProvider)
    {
        $this->finderProvider = $finderProvider;
    }

    /**
     * @Route("", name="apiv2_logs_security", methods={"GET"})
     */
    public function listAction(Request $request): JsonResponse
    {
        $this->canOpenAdminTool('dashboard');

        return new JsonResponse($this->finderProvider->search(
            SecurityLog::class,
            $request->query->all(),
            []
        ));
    }
}
