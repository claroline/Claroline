<?php

namespace Claroline\BadgeBundle\Controller;

use Claroline\BadgeBundle\Entity\Badge;
use Claroline\BadgeBundle\Entity\BadgeClaim;
use Claroline\BadgeBundle\Form\ClaimBadgeType;
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
     *
     * @Template()
     */
    public function claimAction(Request $request)
    {
        /** @var \Claroline\CoreBundle\Entity\User $user */
        $user = $this->get('security.context')->getToken()->getUser();

        $badgeClaim = new BadgeClaim();
        $badgeClaim->setUser($user);
        $form = $this->createForm(new ClaimBadgeType(), $badgeClaim);

        if($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
                $translator = $this->get('translator');
                try {
                    /** @var \Doctrine\ORM\EntityManager $entityManager */
                    $entityManager = $this->getDoctrine()->getManager();

                    $badgeName = $form->get('badge')->getData();

                    $badge = $entityManager->getRepository('ClarolineBadgeBundle:Badge')->findOneByName($badgeName);

                    if($user->hasBadge($badge)) {
                        $this->get('session')->getFlashBag()->add('alert', $translator->trans('badge_already_award_message', array(), 'platform'));
                    }
                    elseif($user->hasClaimedFor($badge)) {
                        $this->get('session')->getFlashBag()->add('alert', $translator->trans('badge_already_claim_message', array(), 'platform'));
                    }
                    else {
                        $badgeClaim->setBadge($badge);

                        $entityManager->persist($badgeClaim);
                        $entityManager->flush();

                        $this->get('session')->getFlashBag()->add('success', $translator->trans('badge_claim_success_message', array(), 'platform'));
                    }
                }
                catch(NoResultException $exception) {
                    $this->get('session')->getFlashBag()->add('error', $translator->trans('badge_not_found_with_name', array('%badgeName%' => $badgeName), 'platform'));
                }
                catch(\Exception $exception) {
                    $this->get('session')->getFlashBag()->add('error', $translator->trans('badge_claim_error_message', array(), 'platform'));
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
     * @Template()
     */
    public function badgeAction(Badge $badge)
    {
        $user = $this->get('security.context')->getToken()->getUser();

        $userBadge = $this->getDoctrine()->getRepository('ClarolineBadgeBundle:UserBadge')->findOneByBadgeAndUser($badge, $user);

        /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler $platformConfigHandler */
        $platformConfigHandler = $this->get('claroline.config.platform_config_handler');

        $badge->setLocale($platformConfigHandler->getParameter('locale_language'));

        return array(
            'userBadge' => $userBadge,
            'badge'     => $badge
        );
    }

    /**
     * @Route("/{page}", name="claro_profile_view_badges")
     * @Template()
     */
    public function badgesAction($page = 1)
    {
        $user = $this->get('security.context')->getToken()->getUser();

        $query = $this->getDoctrine()->getRepository('ClarolineBadgeBundle:Badge')->findByUser($user, true);
        $adapter = new DoctrineORMAdapter($query);
        $pager   = new Pagerfanta($adapter);
        $pager
            ->setMaxPerPage(10)
            ->setCurrentPage($page)
        ;

        $badgeClaims = $this->getDoctrine()->getRepository('ClarolineBadgeBundle:BadgeClaim')->findByUser($user);

        /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler $platformConfigHandler */
        $platformConfigHandler = $this->get('claroline.config.platform_config_handler');

        return array(
            'pager'         => $pager,
            'badgeClaims'   => $badgeClaims,
            'language'      => $platformConfigHandler->getParameter('locale_language')
        );
    }
}