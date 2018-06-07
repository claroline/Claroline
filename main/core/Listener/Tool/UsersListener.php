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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service
 */
class UsersListener
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var TwigEngine */
    private $templating;
    /** @var ObjectManager */
    private $om;

    /**
     * UsersListener constructor.
     *
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "templating"    = @DI\Inject("templating"),
     *     "om"            = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param TwigEngine                    $templating
     * @param ObjectManager                 $om
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TwigEngine $templating,
        ObjectManager $om
    ) {
        $this->authorization = $authorization;
        $this->templating = $templating;
        $this->om = $om;
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

        $content = $this->templating->render(
            'ClarolineCoreBundle:workspace:users.html.twig', [
                'workspace' => $workspace,
                'restrictions' => [
                    'hasUserManagementAccess' => $this->authorization->isGranted('OPEN', $this->om
                        ->getRepository('ClarolineCoreBundle:Tool\AdminTool')
                        ->findOneBy(['name' => 'user_management'])
                    ),
                ],
            ]
        );

        $event->setContent($content);
        $event->stopPropagation();
    }
}
