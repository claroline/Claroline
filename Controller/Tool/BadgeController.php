<?php

namespace Icap\BadgeBundle\Controller\Tool;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Icap\BadgeBundle\Entity\Badge;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BadgeController extends Controller
{
    public function myWorkspaceBadgeAction(Workspace $workspace, User $loggedUser, $badgePage)
    {
        /** @var \Claroline\CoreBundle\Rule\Validator $badgeRuleValidator */
        $badgeRuleValidator = $this->get("claroline.rule.validator");

        /** @var \Icap\BadgeBundle\Entity\Badge[] $workspaceBadges */
        $workspaceBadges = $this->getDoctrine()->getManager()->getRepository('IcapBadgeBundle:Badge')->findByWorkspace($workspace);

        $ownedBadges      = array();
        $finishedBadges   = array();
        $inProgressBadges = array();
        $availableBadges  = array();
        $displayedBadges  = array();

        foreach ($workspaceBadges as $workspaceBadge) {
            $isOwned = false;
            foreach ($workspaceBadge->getUserBadges() as $userBadge) {
                if ($loggedUser->getId() === $userBadge->getUser()->getId()) {
                    $ownedBadges[] = $userBadge;
                    $isOwned = true;
                }
            }

            if (!$isOwned) {
                $nbBadgeRules      = count($workspaceBadge->getRules());
                $validatedRules    = $badgeRuleValidator->validate($workspaceBadge, $loggedUser);

                if(0 < $nbBadgeRules && 0 < $validatedRules['validRules']) {
                    if ($validatedRules['validRules'] >= $nbBadgeRules) {
                        $finishedBadges[] = $workspaceBadge;
                    } else {
                        $inProgressBadges[] = $workspaceBadge;
                    }
                }
                else {
                    $availableBadges[] = $workspaceBadge;
                }
            }
        }

        // Create badge list to display (owned badges first, in progress badges and then other badges)
        $displayedBadges = array();
        foreach ($ownedBadges as $ownedBadge) {
            $displayedBadges[] = array(
                'type'  => 'owned',
                'badge' => $ownedBadge
            );
        }

        foreach ($finishedBadges as $finishedBadge) {
            $badgeType = 'finished';

            if($loggedUser->hasClaimedFor($finishedBadge)) {
                $badgeType = 'claimed';
            }

            $displayedBadges[] = array(
                'type'  => $badgeType,
                'badge' => $finishedBadge
            );
        }

        foreach ($inProgressBadges as $inProgressBadge) {
            $displayedBadges[] = array(
                'type'  => 'inprogress',
                'badge' => $inProgressBadge
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

        return $this->render(
            'IcapBadgeBundle:Template:Tool/list.html.twig',
            array(
                'badgePager' => $badgePager,
                'workspace'  => $workspace,
                'badgePage'  => $badgePage
            )
        );
    }
}
