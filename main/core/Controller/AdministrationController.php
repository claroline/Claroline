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

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Manager\ToolManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AdministrationController extends Controller
{
    /** @var StrictDispatcher */
    private $eventDispatcher;

    /** @var ToolManager */
    private $toolManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * AdministrationController constructor.
     *
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "toolManager"     = @DI\Inject("claroline.manager.tool_manager"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage")
     * })
     *
     * @param StrictDispatcher      $eventDispatcher
     * @param ToolManager           $toolManager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        StrictDispatcher $eventDispatcher,
        ToolManager $toolManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->toolManager = $toolManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @EXT\Route(
     *     "/index",
     *     name="claro_admin_index"
     * )
     *
     * Displays the administration section index.
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $tools = $this->toolManager->getAdminToolsByRoles($this->tokenStorage->getToken()->getRoles());

        if (count($tools) === 0) {
            throw new AccessDeniedException();
        }

        return $this->redirect($this->generateUrl('claro_admin_open_tool', ['toolName' => $tools[0]->getName()]));
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
        /** @var OpenAdministrationToolEvent $event */
        $event = $this->eventDispatcher->dispatch(
            'administration_tool_'.$toolName,
            'OpenAdministrationTool',
            ['toolName' => $toolName]
        );

        return $event->getResponse();
    }

    /**
     * @EXT\Template("ClarolineCoreBundle:administration:toolbar.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function renderToolbarAction(Request $request)
    {
        $tools = $this->toolManager->getAdminToolsByRoles($this->tokenStorage->getToken()->getRoles());

        $current = null;
        if ('claro_admin_open_tool' === $request->get('_route')) {
            $params = $request->get('_route_params');
            if (!empty($params['toolName'])) {
                $current = $params['toolName'];
            }
        }

        return [
            'current' => $current,
            'tools' => array_map(function (AdminTool $tool) {
                return [
                    'icon' => $tool->getClass(),
                    'name' => $tool->getName(),
                    'open' => ['claro_admin_open_tool', ['toolName' => $tool->getName()]]
                ];
            }, $tools),
        ];
    }
}
