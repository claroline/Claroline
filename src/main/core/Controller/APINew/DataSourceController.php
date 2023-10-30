<?php

namespace Claroline\CoreBundle\Controller\APINew;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Manager\DataSourceManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Exposes platform data sources.
 *
 * @Route("/data_source", options={"expose": true})
 */
class DataSourceController
{
    public function __construct(
        private readonly SerializerProvider $serializer,
        private readonly DataSourceManager $manager
    ) {
    }

    /**
     * Lists available data sources for a given context.
     *
     * @Route("/{context}", name="apiv2_data_source_list", defaults={"context"=null}, methods={"GET"})
     */
    public function listAction(string $context = null): JsonResponse
    {
        $dataSources = $this->manager->getAvailable($context);

        return new JsonResponse(array_map(function (DataSource $dataSource) {
            return $this->serializer->serialize($dataSource);
        }, $dataSources));
    }

    /**
     * Gets data from a data source.
     *
     * @Route("/{type}/{context}/{contextId}", name="apiv2_data_source", defaults={"contextId"=null}, methods={"GET"})
     */
    public function loadAction(Request $request, string $type, string $context, string $contextId = null): JsonResponse
    {
        if (!$this->manager->check($type, $context)) {
            return new JsonResponse('Unknown data source.', 404);
        }

        return new JsonResponse(
            $this->manager->load($type, $context, $contextId, $request->query->all())
        );
    }
}
