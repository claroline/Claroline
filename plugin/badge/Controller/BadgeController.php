<?php

namespace Icap\BadgeBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Rule\Validator;
use Doctrine\ORM\QueryBuilder;
use Icap\BadgeBundle\Entity\Badge;
use Icap\BadgeBundle\Manager\BadgeManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class BadgeController extends Controller
{
    public function listAction($parameters)
    {
        /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler $platformConfigHandler */
        $platformConfigHandler = $this->get('claroline.config.platform_config_handler');

        /** @var \Icap\BadgeBundle\Repository\BadgeRepository $badgeRepository */
        $badgeRepository = $this->getDoctrine()->getRepository('IcapBadgeBundle:Badge');
        /** @var QueryBuilder $badgeQueryBuilder */
        $badgeQueryBuilder = $badgeRepository->findOrderedByName(
            $platformConfigHandler->getParameter('locale_language'),
            false
        );

        if (isset($parameters['workspace']) && null !== $parameters['workspace']) {
            $badgeQueryBuilder
                ->andWhere('badge.workspace = :workspace')
                ->setParameter('workspace', $parameters['workspace']);

            $badgeClaimsWorkspace = $parameters['workspace'];
        } else {
            $badgeQueryBuilder->andWhere('badge.workspace IS NULL');
            $badgeClaimsWorkspace = null;
        }

        /** @var \Icap\BadgeBundle\Repository\BadgeClaimRepository $badgeClaimRepository */
        $badgeClaimRepository = $this->getDoctrine()->getRepository('IcapBadgeBundle:BadgeClaim');
        $badgeClaimQuery = $badgeClaimRepository->findByWorkspace($badgeClaimsWorkspace, false);

        $userQuery = $badgeClaimsWorkspace ?
            $this->getDoctrine()->getRepository('ClarolineCoreBundle:User')->findByWorkspaceWithUsersFromGroup($badgeClaimsWorkspace, false) :
            $this->getDoctrine()->getRepository('ClarolineCoreBundle:User')->findAll(false);

        /** @var \Claroline\CoreBundle\Pager\PagerFactory $pagerFactory */
        $pagerFactory = $this->get('claroline.pager.pager_factory');

        $badgePager = $pagerFactory->createPager($badgeQueryBuilder->getQuery(), $parameters['badgePage'], 10);
        $claimPager = $pagerFactory->createPager($badgeClaimQuery, $parameters['claimPage'], 10);
        $userPager = $pagerFactory->createPager($userQuery, $parameters['userPage'], 10);

        /** @var \Icap\BadgeBundle\Manager\BadgeManager $badgeManager */
        $badgeManager = $this->get('icap_badge.manager.badge');
        $badges = $badgeManager->getBadgesByWorkspace($userPager, $badgeClaimsWorkspace, $parameters['userPage'], 10);

        return $this->render(
            'IcapBadgeBundle::Template/list.html.twig',
            [
                'badgePager' => $badgePager,
                'claimPager' => $claimPager,
                'userPager' => $userPager,
                'parameters' => $parameters,
                'badges' => $badges,
                'badgeRuleChecker' => $this->get('claroline.rule.validator'),
            ]
        );
    }

    /**
     * @Route("/badges", name="icap_badge_badge_picker", options={"expose": true})
     * @Method({"POST"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function badgePickerAction(Request $request, User $user)
    {
        /** @var ParameterBag $requestParameters */
        $requestParameters = $request->request;

        /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler $platformConfigHandler */
        $platformConfigHandler = $this->get('claroline.config.platform_config_handler');

        /** @var \Icap\BadgeBundle\Manager\BadgeManager $badgeManager */
        $badgeManager = $this->get('icap_badge.manager.badge');

        $parameters = [
            'locale' => $platformConfigHandler->getParameter('locale_language'),
            'mode' => $requestParameters->get('mode', BadgeManager::BADGE_PICKER_DEFAULT_MODE),
            'user' => $user,
            'workspace' => $requestParameters->get('workspace', null),
            'blacklist' => $requestParameters->get('blacklist', []),
        ];

        $badges = $badgeManager->getForBadgePicker($parameters);

        $value = $requestParameters->get('value', []);

        if (!is_array($value)) {
            $value = [$value];
        }

        return [
            'badges' => $badges,
            'multiple' => $requestParameters->get('multiple', true),
            'value' => $value,
        ];
    }

    /**
     * @Route("/badge/{username}/{badgeSlug}", name="icap_badge_badge_share_view")
     * @Template
     */
    public function shareViewAction(Request $request, $username, $badgeSlug)
    {
        $userBadge = $this->getDoctrine()->getRepository('IcapBadgeBundle:UserBadge')->findOneByUsernameAndBadgeSlug($username, $badgeSlug);

        if ($userBadge === null) {
            throw $this->createNotFoundException('Cannot found badge.');
        }

        if (!$userBadge->isIsShared()) {
            throw $this->createNotFoundException('Badge not shared.');
        }

        if (!$this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $showBanner = false;
        } else {
            $showBanner = ($this->getUser() === $userBadge->getUser());
        }

        return [
            'userBadge' => $userBadge,
            'user' => $userBadge->getUser(),
            'showBanner' => $showBanner,
        ];
    }
}
