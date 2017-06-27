<?php

namespace Icap\BadgeBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Rule\Validator;
use Doctrine\ORM\EntityManager;
use Icap\BadgeBundle\Entity\Badge;
use Icap\BadgeBundle\Entity\BadgeClaim;
use Icap\BadgeBundle\Entity\BadgeTranslation;
use Icap\BadgeBundle\Manager\BadgeManager;
use JMS\DiExtraBundle\Annotation as DI;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller of the badges.
 *
 * @Route("/admin/badges")
 */
class AdministrationController extends Controller
{
    private $userManager;
    private $ruleValidator;
    private $entityManager;
    private $badgeManager;

    /**
     * @DI\InjectParams({
     *     "userManager"      = @DI\Inject("claroline.manager.user_manager"),
     *     "ruleValidator" = @DI\Inject("claroline.rule.validator"),
     *     "entityManager" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "badgeManager" = @DI\Inject("icap_badge.manager.badge")
     * })
     */
    public function __construct(
        UserManager $userManager,
        Validator $ruleValidator,
        EntityManager $entityManager,
        BadgeManager $badgeManager
    ) {
        $this->userManager = $userManager;
        $this->ruleValidator = $ruleValidator;
        $this->entityManager = $entityManager;
        $this->badgeManager = $badgeManager;
    }

    /**
     * @Route(
     *     "/{badgePage}/{claimPage}/{userPage}",
     *     name="icap_badge_admin_badges",
     *     requirements={"badgePage" = "\d+", "userPage" = "\d+", "claimPage" = "\d+"},
     *     defaults={"badgePage" = 1, "claimPage" = 1, "userPage" = 1}
     * )
     *
     * @Template
     */
    public function listAction($badgePage = 1, $claimPage = 1, $userPage = 1)
    {
        $this->checkOpen();

        $parameters = [
            'badgePage' => $badgePage,
            'claimPage' => $claimPage,
            'userPage' => $userPage,
            'add_link' => 'icap_badge_admin_badges_add',
            'edit_link' => [
                'url' => 'icap_badge_admin_badges_edit',
                'suffix' => '#!edit',
            ],
            'delete_link' => 'icap_badge_admin_badges_delete',
            'view_link' => 'icap_badge_admin_badges_edit',
            'current_link' => 'icap_badge_admin_badges',
            'claim_link' => 'icap_badge_admin_manage_claim',
            'statistics_link' => 'icap_badge_admin_badges_statistics',
            'csv_link' => 'icap_badge_export_csv',
            'route_parameters' => [],
        ];

        return [
            'parameters' => $parameters,
        ];
    }

    /**
     * @Route("/add", name="icap_badge_admin_badges_add")
     *
     * @Template()
     */
    public function addAction(Request $request)
    {
        $this->checkOpen();
        $badge = new Badge();

        $locales = $this->get('claroline.manager.locale_manager')->getAvailableLocales();

        foreach ($locales as $locale) {
            $translation = new BadgeTranslation();
            $translation->setLocale($locale);
            $badge->addTranslation($translation);
        }

        /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $sessionFlashBag */
        $sessionFlashBag = $this->get('session')->getFlashBag();

        /** @var \Symfony\Component\Translation\TranslatorInterface $translator */
        $translator = $this->get('translator');

        try {
            if ($this->get('icap_badge.form_handler.badge')->handleAdd($badge)) {
                $sessionFlashBag->add('success', $translator->trans('badge_add_success_message', [], 'icap_badge'));

                return $this->redirect($this->generateUrl('icap_badge_admin_badges'));
            }
        } catch (\Exception $exception) {
            $sessionFlashBag->add('error', $translator->trans('badge_add_error_message', [], 'icap_badge'));

            return $this->redirect($this->generateUrl('icap_badge_admin_badges'));
        }

        return [
            'form' => $this->get('icap_badge.form.badge')->createView(),
            'badge' => $badge,
        ];
    }

    /**
     * @Route("/edit/{slug}/{page}", name="icap_badge_admin_badges_edit")
     * @ParamConverter("badge", converter="badge_converter")
     *
     * @Template()
     */
    public function editAction(Request $request, Badge $badge, $page = 1)
    {
        $this->checkOpen();
        $query = $this->getDoctrine()->getRepository('IcapBadgeBundle:UserBadge')->findByBadge($badge, false);
        $adapter = new DoctrineORMAdapter($query);
        $pager = new Pagerfanta($adapter);

        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $exception) {
            throw $this->createNotFoundException();
        }

        /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $sessionFlashBag */
        $sessionFlashBag = $this->get('session')->getFlashBag();

        /** @var \Symfony\Component\Translation\TranslatorInterface $translator */
        $translator = $this->get('translator');

        try {
            $unawardBadge = $request->query->get('unawardBadge') === 'true';

            if ($this->get('icap_badge.form_handler.badge')->handleEdit($badge, $this->badgeManager, $unawardBadge)) {
                $sessionFlashBag->add('success', $translator->trans('badge_edit_success_message', [], 'icap_badge'));

                return $this->redirect($this->generateUrl('icap_badge_admin_badges'));
            }
        } catch (\Exception $exception) {
            $sessionFlashBag->add('error', $translator->trans('badge_edit_error_message', [], 'icap_badge'));

            return $this->redirect($this->generateUrl('icap_badge_admin_badges'));
        }

        return [
            'form' => $this->get('icap_badge.form.badge')->createView(),
            'badge' => $badge,
            'pager' => $pager,
        ];
    }

    /**
     * @Route("/delete/{slug}", name="icap_badge_admin_badges_delete")
     * @ParamConverter("badge", converter="badge_converter")
     *
     * @Template()
     */
    public function deleteAction(Badge $badge)
    {
        $this->checkOpen();

        if (null !== $badge->getWorkspace()) {
            throw $this->createNotFoundException('No badge found.');
        }

        /** @var \Symfony\Component\Translation\TranslatorInterface $translator */
        $translator = $this->get('translator');
        try {
            /* @var \Doctrine\Common\Persistence\ObjectManager $entityManager */
            $this->get('icap_badge.form_handler.badge')->handleDelete($badge);

            $this->get('session')
                ->getFlashBag()
                ->add('success', $translator->trans('badge_delete_success_message', [], 'icap_badge'));
        } catch (\Exception $exception) {
            $this->get('session')
                ->getFlashBag()
                ->add('error', $translator->trans('badge_delete_error_message', [], 'icap_badge'));
        }

        return $this->redirect($this->generateUrl('icap_badge_admin_badges'));
    }

    /**
     * @Route("/award/{slug}", name="icap_badge_admin_badges_award")
     * @ParamConverter("badge", converter="badge_converter")
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     *
     * @Template()
     */
    public function awardAction(Request $request, Badge $badge, User $loggedUser)
    {
        $this->checkOpen();

        if (null !== $badge->getWorkspace()) {
            throw $this->createNotFoundException('No badge found.');
        }

        $form = $this->createForm($this->get('icap_badge.form.badge.award'));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                /** @var \Symfony\Component\Translation\TranslatorInterface $translator */
                $translator = $this->get('translator');
                try {
                    $doctrine = $this->getDoctrine();

                    $group = $form->get('group')->getData();
                    $user = $form->get('user')->getData();
                    $comment = $form->get('comment')->getData();

                    /** @var \Claroline\CoreBundle\Entity\User[] $users */
                    $users = [];

                    if (null !== $group) {
                        $users = $doctrine->getRepository('ClarolineCoreBundle:User')->findByGroup($group);
                    } elseif (null !== $user) {
                        $users[] = $user;
                    }

                    /** @var \Icap\BadgeBundle\Manager\BadgeManager $badgeManager */
                    $badgeManager = $this->get('icap_badge.manager.badge');
                    $awardedBadge = $badgeManager->addBadgeToUsers($badge, $users, $comment, $loggedUser);

                    $flashMessageType = 'error';

                    if (0 < $awardedBadge) {
                        $flashMessageType = 'success';
                    }

                    $message = $translator->transChoice(
                        'badge_awarded_count_message',
                        $awardedBadge,
                        ['%awaredBadge%' => $awardedBadge],
                        'icap_badge'
                    );
                    $this->get('session')->getFlashBag()->add($flashMessageType, $message);
                } catch (\Exception $exception) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->get('session')
                            ->getFlashBag()
                            ->add('error', $translator->trans('badge_award_error_message', [], 'icap_badge'));
                    } else {
                        return new Response($exception->getMessage(), 500);
                    }
                }

                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse(['error' => false]);
                }

                return $this->redirect($this->generateUrl('icap_badge_admin_badges_edit', ['slug' => $badge->getSlug()]));
            }
        }

        return [
            'badge' => $badge,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/unaward/{slug}/{username}", name="icap_badge_admin_badges_unaward")
     * @ParamConverter("user", options={"mapping": {"username": "username"}})
     * @ParamConverter("badge", converter="badge_converter")
     *
     * @Template()
     */
    public function unawardAction(Request $request, Badge $badge, User $user)
    {
        $this->checkOpen();

        if (null !== $badge->getWorkspace()) {
            throw $this->createNotFoundException('No badge found.');
        }

        /** @var \Symfony\Component\Translation\TranslatorInterface $translator */
        $translator = $this->get('translator');
        try {
            $doctrine = $this->getDoctrine();
            /** @var \Doctrine\ORM\EntityManager $entityManager */
            $entityManager = $doctrine->getManager();

            $userBadge = $doctrine->getRepository('IcapBadgeBundle:UserBadge')
                ->findOneByBadgeAndUser($badge, $user);

            $entityManager->remove($userBadge);
            $entityManager->flush();

            $this->get('session')
                ->getFlashBag()
                ->add('success', $translator->trans('badge_unaward_success_message', [], 'icap_badge'));
        } catch (\Exception $exception) {
            if (!$request->isXmlHttpRequest()) {
                $this->get('session')
                    ->getFlashBag()
                    ->add('error', $translator->trans('badge_unaward_error_message', [], 'icap_badge'));
            } else {
                return new Response($exception->getMessage(), 500);
            }
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['error' => false]);
        }

        return $this->redirect($this->generateUrl('icap_badge_admin_badges_edit', ['slug' => $badge->getSlug()]));
    }

    /**
     * @Route("/claim/manage/{id}/{validate}", name="icap_badge_admin_manage_claim")
     *
     * @Template()
     */
    public function manageClaimAction(BadgeClaim $badgeClaim, $validate = false)
    {
        $this->checkOpen();

        if (null !== $badgeClaim->getBadge()->getWorkspace()) {
            throw $this->createNotFoundException('No badge found.');
        }

        /** @var \Symfony\Component\Translation\TranslatorInterface $translator */
        $translator = $this->get('translator');
        $successMessage = $translator->trans('badge_reject_award_success_message', [], 'icap_badge');
        $errorMessage = $translator->trans('badge_reject_award_error_message', [], 'icap_badge');

        try {
            if ($validate) {
                $successMessage = $translator->trans('badge_validate_award_success_message', [], 'icap_badge');
                $errorMessage = $translator->trans('badge_validate_award_error_message', [], 'icap_badge');

                /** @var \Icap\BadgeBundle\Manager\BadgeManager $badgeManager */
                $badgeManager = $this->get('icap_badge.manager.badge');
                $awardedBadge = $badgeManager->addBadgeToUser($badgeClaim->getBadge(), $badgeClaim->getUser());

                if (!$awardedBadge) {
                    $successMessage = $translator->trans('badge_already_award_info_message', [], 'icap_badge');
                }
            }

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->remove($badgeClaim);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('success', $successMessage);
        } catch (\Exception $exception) {
            $this->get('session')->getFlashBag()->add('error', $errorMessage);
        }

        return $this->redirect($this->generateUrl('icap_badge_admin_badges'));
    }

    /**
     * @Route("/statistics", name="icap_badge_admin_badges_statistics")
     * @Template()
     */
    public function statisticsAction(Request $request)
    {
        $this->checkOpen();

        return [];
    }

    /**
     * @Route("/recalculate/{slug}", name="icap_badge_admin_badges_recalculate")
     *
     * @ParamConverter("badge", converter="badge_converter")
     */
    public function recalculateAction(Request $request, Badge $badge)
    {
        $this->checkOpen();

        // Check rules for already awarded badges ?
        $recalculateAlreadyAwarded = $request->query->get('recalculateAlreadyAwarded') === 'true';

        // Get Users
        $users = $recalculateAlreadyAwarded ?
            $this->userManager->getAll() :
            $this->badgeManager->getUsersNotAwardedWithBadge($badge);

        $nbRules = count($badge->getRules());

        foreach ($users as $user) {
            $resources = $this->ruleValidator->validate($badge, $user);
            if (0 < $resources['validRules'] && $resources['validRules'] >= $nbRules) {
                // Add badge to user but delay flush. It will be performed later, outside foreach loop
                $this->badgeManager->addBadgeToUser($badge, $user, null, null, true);
            } else {
                // Remove badge from user but delay flush. It will be performed later, outside foreach loop
                $this->badgeManager->revokeBadgeFromUser($badge, $user, null, null, true);
            }
        }
        $this->getDoctrine()->getManager()->flush();

        $translator = $this->get('translator');
        $successMessage = $translator->trans('recalculate_success', [], 'icap_badge');
        $this->get('session')->getFlashBag()->add('success', $successMessage);

        return $this->redirect($this->generateUrl('icap_badge_admin_badges'));
    }

    /**
     * @Route("/export", name="icap_badge_export_csv")
     *
     * @param Request $request
     *
     * @return StreamedResponse
     */
    public function exportCSVAction(Request $request)
    {
        $this->checkOpen();

        $locale = $request->getLocale();
        $translator = $this->get('translator');
        $userBadgeRepo = $this->entityManager->getRepository('IcapBadgeBundle:UserBadge');

        $users = $this->getDoctrine()->getRepository('ClarolineCoreBundle:User')->findBy([], ['lastName' => 'ASC', 'firstName' => 'ASC']);
        $badges = $this->badgeManager->getPlatformBadgesOrderedbyName($locale);

        $response = new StreamedResponse(function () use ($users, $badges, $locale, $translator, $userBadgeRepo) {
            $handle = fopen('php://output', 'w+');

            $userTrans = count($users) > 1 ?
                $translator->trans('users', [], 'platform') :
                $translator->trans('user', [], 'platform');
            $badgeTrans = count($badges) > 1 ?
                $translator->trans('badges', [], 'icap_badge') :
                $translator->trans('badge', [], 'icap_badge');

            fputcsv($handle, [count($users).' '.strtolower($userTrans).', '.count($badges).' '.strtolower($badgeTrans)]);

            // Headers
            $headers = [
                $translator->trans('username', [], 'platform'),
                $translator->trans('first_name', [], 'platform'),
                $translator->trans('last_name', [], 'platform'),
            ];
            foreach ($badges as $badge) {
                array_push($headers, $badge->getTranslationForLocale($locale)->getName());
            }
            fputcsv($handle, $headers);

            // Data
            foreach ($users as $user) {
                $line = [
                    $user->getUsername(),
                    $user->getFirstname(),
                    $user->getlastName(),
                ];
                foreach ($badges as $badge) { // foreach iterates always in the same order
                    $check = $userBadgeRepo->findOneByBadgeAndUser($badge, $user) ?
                        'x' : '';
                    array_push($line, $check);
                }
                fputcsv($handle, $line);
            }

            fclose($handle);
        });

        $dateStr = date('Y-m-d');
        $response->headers->set('Content-Type', 'application/force-download');

        $filename = $translator->trans('csv_filename', [], 'icap_badge');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'_'.$dateStr.'.csv"');

        return $response;
    }

    private function checkOpen()
    {
        $badgeAdminTool = $this->container->get('claroline.manager.tool_manager')
            ->getAdminToolByName('badges_management');

        if ($this->container->get('security.authorization_checker')->isGranted('OPEN', $badgeAdminTool)) {
            return true;
        }

        throw new AccessDeniedException();
    }
}
