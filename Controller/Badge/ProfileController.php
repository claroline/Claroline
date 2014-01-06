<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Badge;

use Claroline\CoreBundle\Rule\Validator;
use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\UserBadge;
use Claroline\CoreBundle\Entity\Badge\BadgeClaim;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\NoResultException;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller of the badges.
 *
 * @Route("/profile/badge")
 */
class ProfileController extends Controller
{
    /**
     * @Route("/claim", name="claro_claim_badge")
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function claimAction(Request $request, User $user)
    {
        $badgeClaim = new BadgeClaim();
        $badgeClaim->setUser($user);
        $form = $this->createForm($this->get('claroline.form.claimBadge'), $badgeClaim);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $flashBag = $this->get('session')->getFlashBag();

            if ($form->isValid()) {
                $translator = $this->get('translator');

                try {
                    $entityManager = $this->getDoctrine()->getManager();
                    $badgeName = $form->get('badge')->getData();
                    $badge = $entityManager->getRepository('ClarolineCoreBundle:Badge\Badge')
                        ->findOneByName($badgeName);

                    if ($user->hasBadge($badge)) {
                        $flashBag->add('error', $translator->trans('badge_already_award_message', array(), 'badge'));
                    } elseif ($user->hasClaimedFor($badge)) {
                        $flashBag->add('error', $translator->trans('badge_already_claim_message', array(), 'badge'));
                    } else {
                        $badgeClaim->setBadge($badge);
                        $entityManager->persist($badgeClaim);
                        $entityManager->flush();
                        $flashBag->add('success', $translator->trans('badge_claim_success_message', array(), 'badge'));
                    }
                } catch (NoResultException $exception) {
                    $flashBag->add(
                        'error',
                        $translator->trans('badge_not_found_with_name', array('%badgeName%' => $badgeName), 'badge')
                    );
                } catch (\Exception $exception) {
                    $flashBag->add('error', $translator->trans('badge_claim_error_message', array(), 'badge'));
                }

                return $this->redirect($this->generateUrl('claro_profile_view_badges'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/view/{id}", name="claro_profile_view_badge")
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function badgeAction(Badge $badge, User $user)
    {
        $platformConfigHandler = $this->get('claroline.config.platform_config_handler');
        $badge->setLocale($platformConfigHandler->getParameter('locale_language'));
        $validateLogs = $this->get('claroline.rule.validator')
            ->validate($badge, $user);
        $userBadge = $this->getDoctrine()
            ->getRepository('ClarolineCoreBundle:Badge\UserBadge')
            ->findOneBy(array('badge' => $badge, 'user' => $user));

        return array(
            'userBadge'   => $userBadge,
            'badge'       => $badge,
            'checkedLogs' => $validateLogs
        );
    }

    /**
     * @Route("/{page}", name="claro_profile_view_badges", requirements={"page" = "\d+"}, defaults={"page" = 1})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function badgesAction($page, User $user)
    {
        $query   = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Badge\Badge')->findByUser($user, false);
        $adapter = new DoctrineORMAdapter($query);
        $pager   = new Pagerfanta($adapter);

        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $exception) {
            throw new NotFoundHttpException();
        }

        $badgeClaims = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Badge\BadgeClaim')->findByUser($user);

        /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler $platformConfigHandler */
        $platformConfigHandler = $this->get('claroline.config.platform_config_handler');

        return array(
            'pager'         => $pager,
            'badgeClaims'   => $badgeClaims,
            'language'      => $platformConfigHandler->getParameter('locale_language')
        );
    }
}
