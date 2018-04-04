<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Widget;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Manager\WidgetManager;
use Claroline\CoreBundle\Repository\Widget\WidgetRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Exposes platform widgets.
 *
 * @EXT\Route("/widget", options={"expose": true})
 */
class WidgetController
{
    /** @var SerializerProvider */
    private $serializer;

    /** @var WidgetManager */
    private $manager;

    /**
     * WidgetController constructor.
     *
     * @DI\InjectParams({
     *     "serializer" = @DI\Inject("claroline.api.serializer"),
     *     "manager"    = @DI\Inject("claroline.manager.widget_manager")
     * })
     *
     * @param SerializerProvider $serializer
     * @param WidgetManager      $manager
     */
    public function __construct(SerializerProvider $serializer, WidgetManager $manager)
    {
        $this->serializer = $serializer;
        $this->manager = $manager;
    }

    /**
     * Lists available widgets for a given context.
     *
     * @EXT\Route("/{context}", name="apiv2_widget_available", defaults={"context"=null})
     * @EXT\Method("GET")
     *
     * @param string $context
     *
     * @return JsonResponse
     */
    public function indexAction($context = null)
    {
        $widgets = $this->manager->getAvailable($context);

        return new JsonResponse(array_map(function (Widget $widget) {
            return $this->serializer->serialize($widget);
        }, $widgets));
    }
}
