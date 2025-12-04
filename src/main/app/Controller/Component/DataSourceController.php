<?php

namespace Claroline\AppBundle\Controller\Component;

use Claroline\AppBundle\Component\Context\ContextProvider;
use Claroline\AppBundle\Component\DataSource\DataSourceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Exposes platform data sources.
 */
#[Route(path: '/data_source')]
class DataSourceController
{
    public function __construct(
        private readonly ContextProvider $contextProvider,
        private readonly DataSourceProvider $dataSourceProvider
    ) {
    }

    /**
     * Gets data from a data source.
     */
    #[Route(path: '/{type}/{context}/{contextId}/{all}', name: 'apiv2_data_source', defaults: ['all' => false, 'contextId' => null], methods: ['GET'])]
    public function openAction(
        Request $request,
        string $type,
        string $context,
        ?string $contextId = null,
        ?bool $all = false
    ): Response {
        try {
            $contextHandler = $this->contextProvider->getContext($context, $contextId);
            $contextSubject = $contextHandler->getSubject($contextId);

            $dataSource = $this->dataSourceProvider->getDataSource($type, $context, $contextSubject);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $dataSource->open($context, $contextSubject, !$all, $request);
    }
}
