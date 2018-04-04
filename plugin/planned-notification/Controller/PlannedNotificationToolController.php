<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PlannedNotificationBundle\Controller;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PlannedNotificationToolController extends Controller
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /**
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker")
     * })
     */
    public function __construct(AuthorizationCheckerInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/open",
     *     name="claro_planned_notification_tool_open",
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     *
     * @param Workspace $workspace
     *
     * @return array
     */
    public function toolOpenAction(Workspace $workspace)
    {
        $this->checkToolAccess($workspace, 'OPEN');

        return [
            'workspace' => $workspace,
            'canEdit' => $this->authorization->isGranted(['claroline_planned_notification_tool', 'EDIT'], $workspace),
        ];
    }

    private function checkToolAccess(Workspace $workspace, $right = 'OPEN')
    {
        if (!$this->authorization->isGranted(['claroline_planned_notification_tool', $right], $workspace)) {
            throw new AccessDeniedException();
        }
    }
}
