<?php

namespace Claroline\AppBundle\Controller\Component;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Context\ContextProvider;
use Claroline\AppBundle\Component\DataSource\DataSourceProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Manager\DataSourceManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Exposes platform data sources.
 *
 * @Route("/data_source")
 */
class DataSourceController
{
    public function __construct(
        private readonly DataSourceManager $manager,
        private readonly ContextProvider $contextProvider,
        private readonly DataSourceProvider $dataSourceProvider
    ) {
    }

    /**
     * Gets data from a data source.
     *
     * @Route("/{type}/{context}/{contextId}", name="apiv2_data_source", defaults={"contextId"=null}, methods={"GET"})
     */
    public function loadAction(Request $request, string $type, string $context, string $contextId = null): JsonResponse
    {
        try {
            $contextHandler = $this->contextProvider->getContext($context, $contextId);
            $contextSubject = $contextHandler->getObject($contextId);

            $dataSource = $this->dataSourceProvider->getDataSource($type, $context, $contextSubject);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return new JsonResponse(
            $this->manager->load($type, $context, $contextId, $request->query->all())
        );
    }
}
