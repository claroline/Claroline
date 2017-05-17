<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Tool;

use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Listener\NoHttpRequestException;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service("workspace_role_tool_config_listener")
 */
class UserListener
{
    /**
     * @DI\InjectParams({
     *     "requestStack"   = @DI\Inject("request_stack"),
     *     "ed"             = @DI\Inject("http_kernel")
     * })
     */
    public function __construct(RequestStack $requestStack, HttpKernelInterface $httpKernel)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->httpKernel = $httpKernel;
    }

    /**
     * @DI\Observe("open_tool_workspace_users")
     *
     * @param DisplayToolEvent $event
     *
     * @throws \Claroline\CoreBundle\Listener\NoHttpRequestException
     */
    public function onDisplay(DisplayToolEvent $event)
    {
        if (!$this->request) {
            throw new NoHttpRequestException();
        }

        if ($event->getWorkspace()->isModel()) {
            $subRequest = $this->request->duplicate(
            [],
            null,
            [
                '_controller' => 'ClarolineCoreBundle:Tool\Roles:configureRolePage',
                'workspace' => $event->getWorkspace(),
                'page' => 1,
                'search' => '',
                'max' => 50,
                'order' => 'id',
            ]
        );
        } else {
            $subRequest = $this->request->duplicate(
              [],
              null,
              [
                  '_controller' => 'ClarolineCoreBundle:Tool\Roles:usersList',
                  'workspace' => $event->getWorkspace(),
                  'page' => 1,
                  'search' => '',
                  'max' => 50,
                  'order' => 'id',
              ]
          );
        }

        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
    }
}
