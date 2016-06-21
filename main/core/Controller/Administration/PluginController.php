<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Administration;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Event\StrictDispatcher;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('platform_parameters')")
 */
class PluginController extends Controller
{
    private $eventDispatcher;
    /**
     * @DI\InjectParams({
     *      "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher")
     * })
     */
    public function __construct(
        StrictDispatcher $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @EXT\Route("/", name="claro_admin_plugins")
     * @EXT\Template
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @EXT\Route(
     *     "/plugin/parameters/{pluginShortName}",
     *     name="claro_admin_plugin_parameters",
     *     options={"expose"=true}
     * )
     */
    public function pluginParametersAction($pluginShortName)
    {
        $eventName = strtolower("plugin_options_{$pluginShortName}");
        $event = $this->eventDispatcher->dispatch($eventName, 'PluginOptions', array());

        return $event->getResponse();
    }
}
