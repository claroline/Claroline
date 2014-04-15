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

use Claroline\CoreBundle\Event\StrictDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

class AdministrationToolController extends Controller
{
    private $eventDispatcher;

    /**
     * @DI\InjectParams({"eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher") })
     */
    public function __construct(StrictDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @EXT\Route(
     *    "/open/{toolName}",
     *    name="claro_admin_open_tool",
     *    options = {"expose"=true}
     * )
     *
     * @param $toolName
     *
     * @return Response
     */
    public function openAdministrationToolAction($toolName)
    {
        $event = $this->eventDispatcher->dispatch(
            'administration_tool_' . $toolName ,
            'OpenAdministrationTool',
            array('toolName' => $toolName)
        );

        return $event->getResponse();
    }
}