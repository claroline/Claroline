<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Context\ContextProvider;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Manager\DataSourceManager;
use Claroline\CoreBundle\Manager\WidgetManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Exposes platform widgets.
 */
#[Route(path: '/widget')]
class WidgetController
{
    public function __construct(
        private readonly SerializerProvider $serializer,
        private readonly ContextProvider $contextProvider,
        private readonly WidgetManager $widgetManager,
        private readonly DataSourceManager $dataSourceManager
    ) {
    }

    /**
     * Lists available widgets for a given context.
     */
    #[Route(path: '/{context}/{contextId}', name: 'apiv2_widget_available', methods: ['GET'])]
    public function listAction(string $context, ?string $contextId = null): JsonResponse
    {
        $contextHandler = $this->contextProvider->getContext($context);
        $contextSubject = $contextHandler->getObject($contextId);

        return new JsonResponse([
            'widgets' => array_map(function (Widget $widget) {
                return $this->serializer->serialize($widget);
            }, $this->widgetManager->getAvailable($context)),
            'dataSources' => $this->dataSourceManager->getAvailable($context, $contextSubject),
        ]);
    }
}
