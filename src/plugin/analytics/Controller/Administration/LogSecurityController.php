<?php

namespace Claroline\AnalyticsBundle\Controller\Administration;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\CoreBundle\Entity\Log\LogSecurity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tools/admin/security-logs")
 */
class LogSecurityController extends AbstractSecurityController
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
            LogSecurity::class,
            $request->query->all(),
            []
        ));
    }
}
