<?php

namespace Claroline\CoreBundle\Controller\APINew;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Manager\DataSourceManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Exposes platform data sources.
 *
 * @EXT\Route("/data_source", options={"expose": true})
 */
class DataSourceController
{
    /** @var StrictDispatcher */
    private $eventDispatcher;

    /** @var SerializerProvider */
    private $serializer;

    /** @var DataSourceManager */
    private $manager;

    /**
     * WidgetController constructor.
     *
     * @param StrictDispatcher   $eventDispatcher
     * @param SerializerProvider $serializer
     * @param DataSourceManager  $manager
     */
    public function __construct(
        StrictDispatcher $eventDispatcher,
        SerializerProvider $serializer,
        DataSourceManager $manager)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->serializer = $serializer;
        $this->manager = $manager;
    }

    /**
     * Lists available data sources for a given context.
     *
     * @EXT\Route("/{context}", name="apiv2_data_source_list", defaults={"context"=null})
     * @EXT\Method("GET")
     *
     * @param string $context
     *
     * @return JsonResponse
     */
    public function listAction($context = null)
    {
        $widgets = $this->manager->getAvailable($context);

        return new JsonResponse(array_map(function (DataSource $dataSource) {
            return $this->serializer->serialize($dataSource);
        }, $widgets));
    }

    /**
     * Gets data from a data source.
     *
     * @EXT\Route("/{type}/{context}/{contextId}", name="apiv2_data_source", defaults={"contextId"=null})
     * @EXT\Method("GET")
     *
     * @param Request $request
     * @param string  $type
     * @param string  $context
     * @param string  $contextId
     *
     * @return JsonResponse
     */
    public function loadAction(Request $request, $type, $context, $contextId = null)
    {
        if (!$this->manager->check($type, $context)) {
            return new JsonResponse('Unknown data source.', 404);
        }

        return new JsonResponse(
            $this->manager->load($type, $context, $contextId, $request->query->all())
        );
    }
}
