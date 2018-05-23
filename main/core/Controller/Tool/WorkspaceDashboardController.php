<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\AnalyticsManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class WorkspaceDashboardController extends Controller
{
    private $analyticsManager;
    private $authorization;

    /**
     * @DI\InjectParams({
     *     "analyticsManager" = @DI\Inject("claroline.manager.analytics_manager"),
     *     "authorization"   = @DI\Inject("security.authorization_checker")
     * })
     */
    public function __construct(
        AnalyticsManager $analyticsManager,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->analyticsManager = $analyticsManager;
        $this->authorization = $authorization;
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/dashboard",
     *     name="claro_workspace_analytics"
     * )
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true},
     *      converter="strict_id"
     * )
     * @EXT\Template("ClarolineCoreBundle:tool/workspace/dashboard:index.html.twig")
     *
     * Displays activities evaluations home tab of analytics tool
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return array
     */
    public function indexAction(Workspace $workspace)
    {
        if (!$this->authorization->isGranted('analytics', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        return [
            'workspace' => $workspace,
        ];
    }
}
