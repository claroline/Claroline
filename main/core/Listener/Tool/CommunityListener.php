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

use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\API\Serializer\User\ProfileSerializer;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service
 */
class CommunityListener
{
    /**
     * CommunityListener constructor.
     *
     * @DI\InjectParams({
     *     "authorization"        = @DI\Inject("security.authorization_checker"),
     *     "parametersSerializer" = @DI\Inject("Claroline\CoreBundle\API\Serializer\ParametersSerializer"),
     *     "profileSerializer"    = @DI\Inject("Claroline\CoreBundle\API\Serializer\User\ProfileSerializer")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ParametersSerializer          $parametersSerializer
     * @param ProfileSerializer             $profileSerializer
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ParametersSerializer $parametersSerializer,
        ProfileSerializer $profileSerializer
    ) {
        $this->authorization = $authorization;
        $this->parametersSerializer = $parametersSerializer;
        $this->profileSerializer = $profileSerializer;
    }

    /**
     * Displays users on Workspace.
     *
     * @DI\Observe("open_tool_workspace_community")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspace(DisplayToolEvent $event)
    {
        $event->setData([
            'profile' => $this->profileSerializer->serialize(),
            'parameters' => $this->parametersSerializer->serialize()['profile'],
            'restrictions' => [
                // TODO: computes rights more accurately
                'hasUserManagementAccess' => $this->authorization->isGranted('ROLE_ADMIN'),
            ],
        ]);

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_tool_desktop_community")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktop(DisplayToolEvent $event)
    {
        $event->setData([
            'restrictions' => [],
            'profile' => $this->profileSerializer->serialize(),
            'parameters' => $this->parametersSerializer->serialize()['profile'],
        ]);

        $event->stopPropagation();
    }
}
