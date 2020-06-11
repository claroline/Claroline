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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\Log\LogDesktopToolReadEvent;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * User desktop.
 *
 * @EXT\Route("/desktop", options={"expose"=true})
 */
class DesktopController
{
    use RequestDecoderTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var ParametersSerializer */
    private $parametersSerializer;

    /** @var SerializerProvider */
    private $serializer;

    /** @var ToolManager */
    private $toolManager;

    /**
     * DesktopController constructor.
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param EventDispatcherInterface      $eventDispatcher
     * @param ParametersSerializer          $parametersSerializer
     * @param SerializerProvider            $serializer
     * @param ToolManager                   $toolManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        EventDispatcherInterface $eventDispatcher,
        ParametersSerializer $parametersSerializer,
        SerializerProvider $serializer,
        ToolManager $toolManager
    ) {
        $this->authorization = $authorization;
        $this->eventDispatcher = $eventDispatcher;
        $this->parametersSerializer = $parametersSerializer;
        $this->serializer = $serializer;
        $this->toolManager = $toolManager;
    }

    /**
     * Opens the desktop.
     *
     * @EXT\Route("/", name="claro_desktop_open")
     * @EXT\ParamConverter("currentUser", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param User $currentUser
     *
     * @return JsonResponse
     */
    public function openAction(User $currentUser = null)
    {
        // TODO : manage anonymous. This will break like this imo but they need to have access to tools opened to them.
        if (empty($currentUser)) {
            throw new AccessDeniedException();
        }

        $orderedTools = $this->toolManager->getOrderedToolsByDesktop($currentUser);
        if (0 === count($orderedTools)) {
            throw new AccessDeniedException('no tools');
        }

        /** @var GenericDataEvent $event */
        $event = $this->eventDispatcher->dispatch('desktop.open', new GenericDataEvent());

        $parameters = $this->parametersSerializer->serialize([Options::SERIALIZE_MINIMAL]);

        return new JsonResponse(array_merge($event->getResponse() ?? [], [
            'userProgression' => null,
            'tools' => array_values(array_map(function (OrderedTool $orderedTool) {
                return $this->serializer->serialize($orderedTool->getTool(), [Options::SERIALIZE_MINIMAL]);
            }, $orderedTools)),
            'shortcuts' => isset($parameters['desktop_shortcuts']) ? $parameters['desktop_shortcuts'] : [],
        ]));
    }

    /**
     * Opens a tool.
     *
     * @EXT\Route("/tool/{toolName}", name="claro_desktop_open_tool")
     *
     * @param string $toolName
     *
     * @return JsonResponse
     */
    public function openToolAction($toolName)
    {
        $orderedTool = $this->toolManager->getOrderedTool($toolName, Tool::DESKTOP);
        if (!$orderedTool) {
            throw new NotFoundHttpException(sprintf('Tool "%s" not found', $toolName));
        }

        if (!$this->authorization->isGranted('OPEN', $orderedTool)) {
            throw new AccessDeniedException();
        }

        /** @var OpenToolEvent $event */
        $event = $this->eventDispatcher->dispatch('open_tool_desktop_'.$toolName, new OpenToolEvent());

        $this->eventDispatcher->dispatch('log', new LogDesktopToolReadEvent($toolName));

        return new JsonResponse(array_merge($event->getData(), [
            'data' => $this->serializer->serialize($orderedTool),
        ]));
    }

    /**
     * Lists desktop tools accessible by the current user.
     *
     * @EXT\Route("/tools", name="claro_desktop_tools")
     * @EXT\ParamConverter("currentUser", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param User|null $currentUser
     *
     * @return JsonResponse
     */
    public function listToolsAction(User $currentUser = null)
    {
        $orderedTools = $this->toolManager->getOrderedToolsByDesktop($currentUser);

        return new JsonResponse(array_values(array_map(function (OrderedTool $orderedTool) {
            return $this->serializer->serialize($orderedTool->getTool(), [Options::SERIALIZE_MINIMAL]);
        }, $orderedTools)));
    }
}
