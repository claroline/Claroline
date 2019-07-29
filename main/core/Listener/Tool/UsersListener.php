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

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service
 */
class UsersListener
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var SerializerProvider */
    private $serializer;

    /**
     * UsersListener constructor.
     *
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "serializer"    = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param SerializerProvider            $serializer
     */
    public function __construct(AuthorizationCheckerInterface $authorization, SerializerProvider $serializer)
    {
        $this->authorization = $authorization;
        $this->serializer = $serializer;
    }

    /**
     * Displays users on Workspace.
     *
     * @DI\Observe("open_tool_workspace_users")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspace(DisplayToolEvent $event)
    {
        $workspace = $event->getWorkspace();

        $event->setData([
            'parameters' => $this->serializer->serialize($workspace),
            'restrictions' => [
                // TODO: computes rights more accurately
                'hasUserManagementAccess' => $this->authorization->isGranted('ROLE_ADMIN'),
            ],
        ]);
        $event->stopPropagation();
    }
}
