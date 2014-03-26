<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Badge\Tool;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\BadgeClaim;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Badge\BadgeTranslation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/workspace/{workspaceId}/my_badges")
 */
class MyWorkspaceController extends Controller
{
    /**
     * @Route(
     *     "/{badgePage}",
     *     name="claro_workspace_tool_my_badges",
     *     requirements={"badgePage" = "\d+"},
     *     defaults={"badgePage" = 1}
     * )
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @ParamConverter(
     *     "workspace",
     *     class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *     options={"id" = "workspaceId"}
     * )
     * @Template
     */
    public function listAction(AbstractWorkspace $workspace, User $loggedUser, $badgePage)
    {
        $this->checkUserIsAllowed($workspace);

        /** @var \Claroline\CoreBundle\Entity\Badge\Badge[] $workspaceBadges */
        $workspaceBadges = $this->getDoctrine()->getManager()->getRepository('ClarolineCoreBundle:Badge\Badge')->findByWorkspace($workspace);

        $ownedBadges = array();
        $availableBadges = array();
        $displayedBadges = array();

        foreach ($workspaceBadges as $workspaceBadge) {
            $isOwned = false;
            foreach ($workspaceBadge->getUserBadges() as $userBadge) {
                if ($loggedUser->getId() === $userBadge->getUser()->getId()) {
                    $ownedBadges[] = $userBadge;
                    $isOwned = true;
                }
            }

            if (!$isOwned) {
                $availableBadges[] = $workspaceBadge;
            }
        }

        // Create badge list to display (owned badge first, then other badge)
        $displayedBadges = array();
        foreach ($ownedBadges as $ownedBadge) {
            $displayedBadges[] = array(
                'type'  => 'owned',
                'badge' => $ownedBadge
            );
        }

        foreach ($availableBadges as $availableBadge) {
            $displayedBadges[] = array(
                'type'  => 'available',
                'badge' => $availableBadge
            );
        }

        /** @var \Claroline\CoreBundle\Pager\PagerFactory $pagerFactory */
        $pagerFactory = $this->get('claroline.pager.pager_factory');
        $badgePager   = $pagerFactory->createPagerFromArray($displayedBadges, $badgePage, 10);

        return array(
            'badgePager' => $badgePager,
            'workspace'  => $workspace,
            'badgePage'  => $badgePage
        );
    }

    private function checkUserIsAllowed(AbstractWorkspace $workspace)
    {
        if (!$this->get('security.context')->isGranted('my_badges', $workspace)) {
            throw new AccessDeniedException();
        }
    }
}
