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

use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Event\Log\LogAdminToolReadEvent;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Manager\ToolManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @EXT\Route("/admin", options={"expose"=true})
 */
class AdministrationController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var ToolManager */
    private $toolManager;

    /**
     * AdministrationController constructor.
     *
     * @DI\InjectParams({
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "toolManager"     = @DI\Inject("claroline.manager.tool_manager")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param TokenStorageInterface         $tokenStorage
     * @param EventDispatcherInterface      $eventDispatcher
     * @param ToolManager                   $toolManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher,
        ToolManager $toolManager
    ) {
        $this->authorization = $authorization;
        $this->eventDispatcher = $eventDispatcher;
        $this->toolManager = $toolManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Opens the administration index.
     *
     * @EXT\Route("/", name="claro_admin_index")
     * @EXT\Route("/", name="claro_admin_open")
     *
     * @throws AccessDeniedException
     *
     * @return JsonResponse
     */
    public function openAction()
    {
        $tools = $this->toolManager->getAdminToolsByRoles($this->tokenStorage->getToken()->getRoles());
        if (0 === count($tools)) {
            throw new AccessDeniedException();
        }

        return new JsonResponse([
            'tools' => array_values(array_map(function (AdminTool $orderedTool) {
                return [
                    'icon' => $orderedTool->getClass(),
                    'name' => $orderedTool->getName(),
                ];
            }, $tools)),
        ]);
    }

    /**
     * Opens an administration tool.
     *
     * @EXT\Route("/open/{toolName}", name="claro_admin_open_tool")
     *
     * @param $toolName
     *
     * @throws AccessDeniedException
     *
     * @return JsonResponse
     */
    public function openToolAction($toolName)
    {
        $tool = $this->toolManager->getAdminToolByName($toolName);
        if (!$tool) {
            throw new NotFoundHttpException('Tool not found');
        }

        if (!$this->authorization->isGranted('OPEN', $tool)) {
            throw new AccessDeniedException();
        }

        /** @var OpenAdministrationToolEvent $event */
        $event = $this->eventDispatcher->dispatch('administration_tool_'.$toolName, new OpenAdministrationToolEvent());

        $this->eventDispatcher->dispatch('log', new LogAdminToolReadEvent($toolName));

        return new JsonResponse($event->getData());
    }

    /**
     * Lists admin tools accessible by the current user.
     *
     * @EXT\Route("/tools", name="claro_admin_tools")
     *
     * @return JsonResponse
     */
    public function listToolsAction()
    {
        $tools = $this->toolManager->getAdminToolsByRoles($this->tokenStorage->getToken()->getRoles());

        return new JsonResponse([
            'tools' => array_values(array_map(function (AdminTool $orderedTool) {
                return [
                    'icon' => $orderedTool->getClass(),
                    'name' => $orderedTool->getName(),
                ];
            }, $tools)),
        ]);
    }
}
