<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Tool;

use Claroline\AppBundle\Controller\AbstractApiController;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\LogConnectManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route("/tool")
 */
class ToolController extends AbstractApiController
{
    /** @var ToolManager */
    private $toolManager;
    /** @var LogConnectManager */
    private $logConnectManager;

    /**
     * ToolController constructor.
     *
     * @param ToolManager       $toolManager
     * @param LogConnectManager $logConnectManager
     */
    public function __construct(ToolManager $toolManager, LogConnectManager $logConnectManager)
    {
        $this->toolManager = $toolManager;
        $this->logConnectManager = $logConnectManager;
    }

    /**
     * @EXT\Route(
     *     "/desktop/tool/configure",
     *     name="apiv2_desktop_tools_configure",
     *     options={"expose"=true}
     * )
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Request $request
     * @param User    $user
     *
     * @return JsonResponse
     */
    public function configureUserOrderedToolsAction(Request $request, User $user)
    {
        $toolsConfig = $this->decodeRequest($request);
        $this->toolManager->saveUserOrderedTools($user, $toolsConfig);

        return new JsonResponse($toolsConfig);
    }

    /**
     * @EXT\Route(
     *     "/tool/close",
     *     name="apiv2_tool_close"
     * )
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param Request $request
     * @param User    $user
     *
     * @return JsonResponse
     */
    public function closeAction(Request $request, User $user = null)
    {
        if ($user) {
            $data = $this->decodeRequest($request);
            $this->logConnectManager->computeToolDuration($user, $data['toolName'], $data['context']);
        }

        return new JsonResponse();
    }
}
