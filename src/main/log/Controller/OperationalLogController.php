<?php

namespace Claroline\LogBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Component\Context\ContextProvider;
use Claroline\LogBundle\Entity\OperationalLog;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(path: '/log/operational')]
class OperationalLogController
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ContextProvider $contextProvider,
        private readonly Crud $crud
    ) {
    }

    #[Route(path: '/{context}/{contextId}', name: 'apiv2_logs_operational', methods: ['GET'])]
    public function listAction(
        string $context,
        string $contextId = null,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        try {
            $contextHandler = $this->contextProvider->getContext($context, $contextId);
            $contextSubject = $contextHandler->getObject($contextId);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        if (!$this->authorization->isGranted('EDIT', $contextSubject)) {
            throw new AccessDeniedException();
        }

        $finderQuery
            ->addFilter('contextId', $contextSubject?->getUuid())
            ->addFilter('contextName', $context);

        $logs = $this->crud->search(OperationalLog::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $logs->toResponse();
    }
}
