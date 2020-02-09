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
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Tool\ConfigureToolEvent;
use Claroline\CoreBundle\Manager\LogConnectManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @EXT\Route("/tool")
 */
class ToolController extends AbstractApiController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ToolManager */
    private $toolManager;
    /** @var LogConnectManager */
    private $logConnectManager;

    /**
     * ToolController constructor.
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ToolManager                   $toolManager
     * @param LogConnectManager             $logConnectManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ToolManager $toolManager,
        LogConnectManager $logConnectManager
    ) {
        $this->authorization = $authorization;
        $this->toolManager = $toolManager;
        $this->logConnectManager = $logConnectManager;
    }

    /**
     * @EXT\Route("/configure/{name}/{context}/{contextId}", name="apiv2_tool_configure")
     * @EXT\Method("PUT")
     *
     * @param Request $request
     * @param string  $name
     * @param string  $context
     * @param string  $contextId
     *
     * @return JsonResponse
     */
    public function configureAction(Request $request, $name, $context, $contextId = null)
    {
        /** @var Tool|AdminTool $tool */
        $tool = null;
        switch ($context) {
            case Tool::ADMINISTRATION:
                $tool = $this->toolManager->getAdminToolByName($name);
                break;
            default:
                $tool = $this->toolManager->getToolByName($name);
                break;
        }

        if (!$tool) {
            throw new NotFoundHttpException(sprintf('Tool "%s" not found', $name));
        }

        if (!$this->authorization->isGranted('EDIT', $tool)) {
            throw new AccessDeniedException();
        }

        /** @var ConfigureToolEvent $event */
        $event = $this->eventDispatcher->dispatch($context.'.'.$name.'.configure', new ConfigureToolEvent($this->decodeRequest($request)));

        return new JsonResponse(
            $event->getUpdated()
        );
    }

    /**
     * @EXT\Route("/close/{name}/{context}/{contextId}", name="apiv2_tool_close")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param User   $user
     * @param string $name
     * @param string $context
     * @param string $contextId
     *
     * @return JsonResponse
     */
    public function closeAction(User $user = null, $name, $context, $contextId = null)
    {
        if ($user) {
            $this->logConnectManager->computeToolDuration($user, $name, $context, $contextId);
        }

        return new JsonResponse();
    }
}
