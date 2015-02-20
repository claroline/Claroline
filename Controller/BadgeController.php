<?php

namespace Icap\BadgeBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\BadgeManager;
use Claroline\CoreBundle\Rule\Validator;
use Claroline\CoreBundle\Entity\Badge\Badge;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class BadgeController extends Controller
{
    public function listAction($parameters)
    {
        /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler $platformConfigHandler */
        $platformConfigHandler = $this->get('claroline.config.platform_config_handler');

        /** @var \Claroline\CoreBundle\Repository\Badge\BadgeRepository $badgeRepository */
        $badgeRepository = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Badge\Badge');
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

        /** @var \Claroline\CoreBundle\Repository\Badge\BadgeClaimRepository $badgeClaimRepository */
        $badgeClaimRepository = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Badge\BadgeClaim');
        /** @var Query $badgeClaimQuery */
        $badgeClaimQuery      = $badgeClaimRepository->findByWorkspace($badgeClaimsWorkspace, false);

        /** @var \Claroline\CoreBundle\Repository\UserRepository $userRepository */
        $userRepository = $this->getDoctrine()->getRepository('ClarolineCoreBundle:User');
        /** @var Query $userQuery */
        $userQuery = $userRepository->findUsersWithBadgesByWorkspace($badgeClaimsWorkspace, false);

        $pagerFactory       = $this->get('claroline.pager.pager_factory');
        $badgePager         = $pagerFactory->createPager($badgeQueryBuilder->getQuery(), $parameters['badgePage'], 10);
        $claimPager         = $pagerFactory->createPager($badgeClaimQuery, $parameters['claimPage'], 10);
        $userPager          = $pagerFactory->createPager($userQuery, $parameters['userPage'], 10);
        $badgeRuleValidator = $this->get("claroline.rule.validator");


        return $this->render(
            'ClarolineCoreBundle:Badge:Template/list.html.twig',
            array(
                'badgePager'       => $badgePager,
                'claimPager'       => $claimPager,
                'userPager'        => $userPager,
                'parameters'       => $parameters,
                'badgeRuleChecker' => $badgeRuleValidator
            )
        );
    }

    /**
     * @Route("/badges", name="claro_badge_picker", options={"expose": true})
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

        /** @var \CLaroline\CoreBundle\Manager\BadgeManager $badgeManager */
        $badgeManager = $this->get('claroline.manager.badge');

        $parameters = array(
            'locale'    => $platformConfigHandler->getParameter('locale_language'),
            'mode'      => $requestParameters->get('mode', BadgeManager::BADGE_PICKER_DEFAULT_MODE),
            'user'      => $user,
            'workspace' => $requestParameters->get('workspace', null),
            'blacklist' => $requestParameters->get('blacklist', array())
        );

        $badges = $badgeManager->getForBadgePicker($parameters);

        $value = $requestParameters->get('value', array());

        if (!is_array($value)) {
            $value = array($value);
        }

        return array(
            'badges'   => $badges,
            'multiple' => $requestParameters->get('multiple', true),
            'value'    => $value
        );
    }
}
