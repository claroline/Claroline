<?php

namespace Claroline\LogBundle\Controller;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\LogBundle\Entity\MessageLog;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/log/message")
 */
class MessageLogController extends AbstractSecurityController
{
    private $finderProvider;

    public function __construct(FinderProvider $finderProvider)
    {
        $this->finderProvider = $finderProvider;
    }

    /**
     * @Route("", name="apiv2_logs_message", methods={"GET"})
     */
    public function listAction(Request $request): JsonResponse
    {
        $this->canOpenAdminTool('logs');

        return new JsonResponse($this->finderProvider->search(
            MessageLog::class,
            $request->query->all(),
            []
        ));
    }
}
