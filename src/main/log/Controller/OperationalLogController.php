<?php

namespace Claroline\LogBundle\Controller;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Component\Context\ContextProvider;
use Claroline\LogBundle\Entity\OperationalLog;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/log/operational")
 */
class OperationalLogController
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly FinderProvider $finder,
        private readonly ContextProvider $contextProvider
    ) {
    }

    /**
     * @Route("/{context}/{contextId}", name="apiv2_logs_operational", methods={"GET"})
     */
    public function listAction(Request $request, string $context, string $contextId = null): JsonResponse
    {
        try {
            $contextHandler = $this->contextProvider->getContext($context, $contextId);
            $contextSubject = $contextHandler->getObject($contextId);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        if (!$this->authorization->isGranted('EDIT', $contextSubject)) {
            throw new AccessDeniedException();
        }

        $query = $request->query->all();
        $query['hiddenFilters'] = [
            'contextName' => $context,
            'contextId' => $contextSubject ? $contextSubject->getUuid() : null,
        ];

        return new JsonResponse(
            $this->finder->search(OperationalLog::class, $query)
        );
    }
}
