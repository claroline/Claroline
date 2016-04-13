<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Event\AdminUserActionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service()
 */
class UserListener
{
    private $container;
    private $httpKernel;

    /**
     * @DI\InjectParams({
     *     "container"  = @DI\Inject("service_container"),
     *     "httpKernel" = @DI\Inject("http_kernel")
     * })
     */
    public function __construct(ContainerInterface $container, HttpKernelInterface $httpKernel)
    {
        $this->container = $container;
        $this->httpKernel = $httpKernel;
    }

    /**
     * @DI\Observe("admin_user_action_edit")
     */
    public function onEditUser(AdminUserActionEvent $event)
    {
        $params = array(
            '_controller' => 'ClarolineCoreBundle:Profile:editProfile',
            'user' => $event->getUser()->getId(),
        );

        $subRequest = $this->container->get('request')->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("admin_user_action_show_workspaces")
     */
    public function onOpenWorkspaceUser(AdminUserActionEvent $event)
    {
        $params = array(
            '_controller' => 'ClarolineCoreBundle:Administration\Users:userWorkspaceList',
            'user' => $event->getUser()->getId(),
            'page' => 1,
            'max' => 50,
        );

        $subRequest = $this->container->get('request')->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}
