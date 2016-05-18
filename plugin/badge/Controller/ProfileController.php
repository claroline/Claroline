<?php

namespace Icap\BadgeBundle\Controller;

use Icap\BadgeBundle\Event\BadgeCreateValidationLinkEvent;
use Icap\BadgeBundle\Entity\Badge;
use Icap\BadgeBundle\Entity\BadgeClaim;
use Claroline\CoreBundle\Entity\User;
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
     * @Route("/claim", name="icap_badge_claim_badge")
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function claimAction(Request $request, User $user)
    {
        $badgeClaim = new BadgeClaim();
        $badgeClaim->setUser($user);
        $form = $this->createForm($this->get('icap_badge.form.claimBadge'), $badgeClaim);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $flashBag = $this->get('session')->getFlashBag();

            if ($form->isValid()) {
                $translator = $this->get('translator');

                try {
                    $entityManager = $this->getDoctrine()->getManager();
                    $badge = $form->get('badge')->getData();

                    if (null !== $badge) {
                        /** @var \Claroline\CoreBundle\Manager\BadgeManager $badgeManager */
                        $badgeManager = $this->get('icap_badge.manager.badge');
                        $badgeManager->makeClaim($badge, $user);

                        $flashBag->add('success', $translator->trans('badge_claim_success_message', array(), 'icap_badge'));
                    } else {
                        $flashBag->add('warning', $translator->trans('badge_claim_nothing_selected_warning_message', array(), 'icap_badge'));
                    }
                } catch (\Exception $exception) {
                    $flashBag->add('error', $translator->trans($exception->getMessage(), array(), 'icap_badge'));
                }

                return $this->redirect($this->generateUrl('icap_badge_profile_view_badges'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/{slug}", name="icap_badge_profile_view_badge")
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @ParamConverter("badge", converter="badge_converter", options={"check_deleted" = false})
     * @Template()
     */
    public function badgeAction(Badge $badge, User $user)
    {
        /** @var \Claroline\CoreBundle\Rule\Validator $badgeRuleValidator */
        $badgeRuleValidator = $this->get('claroline.rule.validator');
        $validatedRules = $badgeRuleValidator->validate($badge, $user);
        $validateLogsLink = array();

        if (0 < $validatedRules['validRules']) {
            foreach ($validatedRules['rules'] as $ruleIndex => $validatedRule) {
                foreach ($validatedRule['logs'] as $logIndex => $validateLog) {
                    $validatedRules['rules'][$ruleIndex]['logs'][$logIndex] = array(
                        'log' => $validateLog,
                        'url' => null,
                    );

                    $validationLink = null;
                    $eventLogName = sprintf('badge-%s-generate_validation_link', $validateLog->getAction());

                    $eventDispatcher = $this->get('event_dispatcher');
                    if ($eventDispatcher->hasListeners($eventLogName)) {
                        $event = $eventDispatcher->dispatch(
                            $eventLogName,
                            new BadgeCreateValidationLinkEvent($validateLog)
                        );

                        $validationLink = $event->getContent();

                        if (null !== $validationLink) {
                            $validatedRules['rules'][$ruleIndex]['logs'][$logIndex]['url'] = $event->getContent();
                        }
                    }
                }
            }
        }

        $userBadge = $this->getDoctrine()->getRepository('IcapBadgeBundle:UserBadge')->findOneBy(array('badge' => $badge, 'user' => $user));

        if (null === $userBadge) {
            throw $this->createNotFoundException("User don't have this badge.");
        }

        return array(
            'userBadge' => $userBadge,
            'badge' => $badge,
            'validatedRules' => $validatedRules,
        );
    }

    /**
     * @Route("/", name="icap_badge_profile_view_badges")
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function badgesAction(User $user)
    {
        $doctrine = $this->getDoctrine();
        $doctrine->getManager()->getFilters()->disable('softdeleteable');
        $userBadges = $doctrine->getRepository('IcapBadgeBundle:UserBadge')->findByUser($user);
        $badgeClaims = $doctrine->getRepository('IcapBadgeBundle:BadgeClaim')->findByUser($user);
        $badgeCollections = $doctrine->getRepository('IcapBadgeBundle:BadgeCollection')->findByUser($user);

        return array(
            'userBadges' => $userBadges,
            'badgeClaims' => $badgeClaims,
            'badgeCollections' => $badgeCollections,
        );
    }
}
