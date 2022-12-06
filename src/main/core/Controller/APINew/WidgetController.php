<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Manager\DataSourceManager;
use Claroline\CoreBundle\Manager\WidgetManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Exposes platform widgets.
 *
 * @Route("/widget", options={"expose": true})
 */
class WidgetController
{
    /** @var SerializerProvider */
    private $serializer;

    /** @var WidgetManager */
    private $widgetManager;

    /** @var DataSourceManager */
    private $dataSourceManager;

    public function __construct(
        SerializerProvider $serializer,
        WidgetManager $widgetManager,
        DataSourceManager $dataSourceManager
    ) {
        $this->serializer = $serializer;
        $this->widgetManager = $widgetManager;
        $this->dataSourceManager = $dataSourceManager;
    }

    /**
     * Lists available widgets for a given context.
     *
     * @Route("/{context}", name="apiv2_widget_available", defaults={"context"=null}, methods={"GET"})
     */
    public function listAction(?string $context = null): JsonResponse
    {
        return new JsonResponse([
            'widgets' => array_map(function (Widget $widget) {
                return $this->serializer->serialize($widget);
            }, $this->widgetManager->getAvailable($context)),
            'dataSources' => array_map(function (DataSource $dataSource) {
                return $this->serializer->serialize($dataSource);
            }, $this->dataSourceManager->getAvailable($context)),
        ]);
    }
}
