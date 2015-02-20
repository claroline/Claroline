<?php

namespace Icap\BadgeBundle\Controller\Tool;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\BadgeClaim;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Badge\BadgeTranslation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
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
 * @Route("/workspace/{workspaceId}/badges")
 */
class WorkspaceController extends Controller
{
    /**
     * @Route(
     *     "/{badgePage}/{claimPage}/{userPage}",
     *     name="claro_workspace_tool_badges",
     *     requirements={"badgePage" = "\d+", "claimPage" = "\d+", "userPage" = "\d+"},
     *     defaults={"badgePage" = 1, "claimPage" = 1, "userPage" = 1}
     * )
     * @ParamConverter(
     *     "workspace",
     *     class="ClarolineCoreBundle:Workspace\Workspace",
     *     options={"id" = "workspaceId"}
     * )
     * @Template
     */
    public function listAction(Workspace $workspace, $badgePage, $claimPage, $userPage)
    {
        $this->checkUserIsAllowed($workspace);

        $parameters = array(
            'badgePage'    => $badgePage,
            'claimPage'    => $claimPage,
            'userPage'    => $userPage,
            'workspace'    => $workspace,
            'add_link'     => 'claro_workspace_tool_badges_add',
            'edit_link'    => array(
                'url'    => 'claro_workspace_tool_badges_edit',
                'suffix' => '#!edit'
            ),
            'delete_link'      => 'claro_workspace_tool_badges_delete',
            'view_link'        => 'claro_workspace_tool_badges_edit',
            'current_link'     => 'claro_workspace_tool_badges',
            'claim_link'       => 'claro_workspace_tool_manage_claim',
            'statistics_link'  => 'claro_workspace_tool_badges_statistics',
            'route_parameters' => array(
                'workspaceId' => $workspace->getId()
            ),
        );

        return array(
            'workspace'   => $workspace,
            'parameters'  => $parameters
        );
    }

    /**
     * @Route("/add", name="claro_workspace_tool_badges_add")
     * @ParamConverter(
     *     "workspace",
     *     class="ClarolineCoreBundle:Workspace\Workspace",
     *     options={"id" = "workspaceId"}
     * )
     * @Template()
     */
    public function addAction(Request $request, Workspace $workspace)
    {
        $this->checkUserIsAllowed($workspace);

        $badge = new Badge();
        $badge->setWorkspace($workspace);

        $locales = $this->get('claroline.common.locale_manager')->getAvailableLocales();
        foreach ($locales as $locale) {
            $translation = new BadgeTranslation();
            $translation->setLocale($locale);
            $badge->addTranslation($translation);
        }

        /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $sessionFlashBag */
        $sessionFlashBag = $this->get('session')->getFlashBag();

        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $this->get('translator');

        try {
            if ($this->get('claroline.form_handler.badge.workspace')->handleAdd($badge)) {
                $sessionFlashBag->add('success', $translator->trans('badge_add_success_message', array(), 'badge'));

                return $this->redirect($this->generateUrl('claro_workspace_tool_badges', array('workspaceId' => $workspace->getId())));
            }
        } catch (\Exception $exception) {
            $sessionFlashBag->add('error', $translator->trans('badge_add_error_message', array(), 'badge'));

            return $this->redirect($this->generateUrl('claro_workspace_tool_badges', array('workspaceId' => $workspace->getId())));
        }

        return array(
            'workspace' => $workspace,
            'form'  => $this->get('claroline.form.badge.workspace')->createView(),
            'badge' => $badge
        );
    }

    /**
     * @Route("/edit/{slug}/{page}", name="claro_workspace_tool_badges_edit")
     * @ParamConverter(
     *     "workspace",
     *     class="ClarolineCoreBundle:Workspace\Workspace",
     *     options={"id" = "workspaceId"}
     * )
     * @ParamConverter("badge", converter="badge_converter")
     * @Template
     */
    public function editAction(Request $request, Workspace $workspace, Badge $badge, $page = 1)
    {
        if (null === $badge->getWorkspace()) {
            throw $this->createNotFoundException("No badge found.");
        }

        $this->checkUserIsAllowed($workspace);

        $query   = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Badge\Badge')->findUsers($badge, false);
        $adapter = new DoctrineORMAdapter($query);
        $pager   = new Pagerfanta($adapter);

        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $exception) {
            throw $this->createNotFoundException();
        }

        /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $sessionFlashBag */
        $sessionFlashBag = $this->get('session')->getFlashBag();

        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $this->get('translator');

        try {
            if ($this->get('claroline.form_handler.badge.workspace')->handleEdit($badge)) {
                $sessionFlashBag->add('success', $translator->trans('badge_edit_success_message', array(), 'badge'));

                return $this->redirect($this->generateUrl('claro_workspace_tool_badges', array('workspaceId' => $workspace->getId())));
            }
        } catch (\Exception $exception) {
            $sessionFlashBag->add('error', $translator->trans('badge_edit_error_message', array(), 'badge'));

            return $this->redirect($this->generateUrl('claro_workspace_tool_badges', array('workspaceId' => $workspace->getId())));
        }

        return array(
            'workspace' => $workspace,
            'form'      => $this->get('claroline.form.badge.workspace')->createView(),
            'badge'     => $badge,
            'pager'     => $pager
        );
    }

    /**
     * @Route("/delete/{slug}", name="claro_workspace_tool_badges_delete")
     * @ParamConverter(
     *     "workspace",
     *     class="ClarolineCoreBundle:Workspace\Workspace",
     *     options={"id" = "workspaceId"}
     * )
     * @ParamConverter("badge", converter="badge_converter")
     * @Template
     */
    public function deleteAction(Workspace $workspace, Badge $badge)
    {
        if (null === $badge->getWorkspace()) {
            throw $this->createNotFoundException("No badge found.");
        }

        $this->checkUserIsAllowed($workspace);

        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $this->get('translator');
        try {
            /** @var \Doctrine\Common\Persistence\ObjectManager $entityManager */
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->remove($badge);
            $entityManager->flush();

            $this->get('session')
                ->getFlashBag()
                ->add('success', $translator->trans('badge_delete_success_message', array(), 'badge'));
        } catch (\Exception $exception) {
            $this->get('session')
                ->getFlashBag()
                ->add('error', $translator->trans('badge_delete_error_message', array(), 'badge'));
        }

        return $this->redirect(
            $this->generateUrl('claro_workspace_tool_badges', array('workspaceId' => $workspace->getId()))
        );
    }

    /**
     * @Route("/award/{slug}", name="claro_workspace_tool_badges_award")
     * @ParamConverter(
     *     "workspace",
     *     class="ClarolineCoreBundle:Workspace\Workspace",
     *     options={"id" = "workspaceId"}
     * )
     * @ParamConverter("badge", converter="badge_converter")
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @Template
     */
    public function awardAction(Request $request, Workspace $workspace, Badge $badge, User $loggedUser)
    {
        if (null === $badge->getWorkspace()) {
            throw $this->createNotFoundException("No badge found.");
        }

        $this->checkUserIsAllowed($workspace);

        $form = $this->createForm($this->get('claroline.form.badge.award'));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
                $translator = $this->get('translator');
                try {
                    $doctrine = $this->getDoctrine();

                    $group   = $form->get('group')->getData();
                    $user    = $form->get('user')->getData();
                    $comment = $form->get('comment')->getData();

                    /** @var \Claroline\CoreBundle\Entity\User[] $users */
                    $users = array();

                    if (null !== $group) {
                        $users = $doctrine->getRepository('ClarolineCoreBundle:User')->findByGroup($group);
                    } elseif (null !== $user) {
                        $users[] = $user;
                    }

                    /** @var \Claroline\CoreBundle\Manager\BadgeManager $badgeManager */
                    $badgeManager = $this->get('claroline.manager.badge');
                    $awardedBadge = $badgeManager->addBadgeToUsers($badge, $users, $comment, $loggedUser);

                    $flashMessageType = 'error';

                    if (0 < $awardedBadge) {
                        $flashMessageType = 'success';
                    }

                    $message = $translator->transChoice(
                        'badge_awarded_count_message',
                        $awardedBadge,
                        array('%awaredBadge%' => $awardedBadge),
                        'badge'
                    );
                    $this->get('session')->getFlashBag()->add($flashMessageType, $message);
                } catch (\Exception $exception) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->get('session')
                            ->getFlashBag()
                            ->add('error', $translator->trans('badge_award_error_message', array(), 'badge'));
                    } else {
                        return new Response($exception->getMessage(), 500);
                    }
                }

                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse(array('error' => false));
                }

                return $this->redirect(
                    $this->generateUrl(
                        'claro_workspace_tool_badges_edit',
                        array('workspaceId' => $workspace->getId(), 'slug' => $badge->getSlug())
                    )
                );
            }
        }

        return array(
            'workspace' => $workspace,
            'badge'     => $badge,
            'form'      => $form->createView()
        );
    }

    /**
     * @Route("/unaward/{id}/{username}", name="claro_workspace_tool_badges_unaward")
     * @ParamConverter(
     *     "workspace",
     *     class="ClarolineCoreBundle:Workspace\Workspace",
     *     options={"id" = "workspaceId"}
     * )
     * @ParamConverter("user", options={"mapping": {"username": "username"}})
     * @Template
     */
    public function unawardAction(Request $request, Workspace $workspace, Badge $badge, User $user)
    {
        if (null === $badge->getWorkspace()) {
            throw $this->createNotFoundException("No badge found.");
        }

        $this->checkUserIsAllowed($workspace);

        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $this->get('translator');
        try {
            $doctrine = $this->getDoctrine();
            /** @var \Doctrine\ORM\EntityManager $entityManager */
            $entityManager = $doctrine->getManager();

            $userBadge = $doctrine->getRepository('ClarolineCoreBundle:Badge\UserBadge')
                ->findOneByBadgeAndUser($badge, $user);

            $entityManager->remove($userBadge);
            $entityManager->flush();

            $this->get('session')
                ->getFlashBag()
                ->add('success', $translator->trans('badge_unaward_success_message', array(), 'badge'));
        } catch (\Exception $exception) {
            if (!$request->isXmlHttpRequest()) {
                $this->get('session')
                    ->getFlashBag()
                    ->add('error', $translator->trans('badge_unaward_error_message', array(), 'badge'));
            } else {
                return new Response($exception->getMessage(), 500);
            }
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(array('error' => false));
        }

        return $this->redirect(
            $this->generateUrl(
                'claro_workspace_tool_badges_edit',
                array('workspaceId' => $workspace->getId(), 'slug' => $badge->getSlug())
            )
        );
    }

    /**
     * @Route("/claim/manage/{id}/{validate}", name="claro_workspace_tool_manage_claim")
     * @ParamConverter(
     *     "workspace",
     *     class="ClarolineCoreBundle:Workspace\Workspace",
     *     options={"id" = "workspaceId"}
     * )
     * @Template
     */
    public function manageClaimAction(Workspace $workspace, BadgeClaim $badgeClaim, $validate = false)
    {
        if (null === $badgeClaim->getBadge()->getWorkspace()) {
            throw $this->createNotFoundException("No badge found.");
        }

        $this->checkUserIsAllowed($workspace);

        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator     = $this->get('translator');
        $successMessage = $translator->trans('badge_reject_award_success_message', array(), 'badge');
        $errorMessage   = $translator->trans('badge_reject_award_error_message', array(), 'badge');

        try {
            if ($validate) {
                $successMessage = $translator->trans('badge_validate_award_success_message', array(), 'badge');
                $errorMessage   = $translator->trans('badge_validate_award_error_message', array(), 'badge');

                /** @var \Claroline\CoreBundle\Manager\BadgeManager $badgeManager */
                $badgeManager = $this->get('claroline.manager.badge');
                $awardedBadge = $badgeManager->addBadgeToUsers($badgeClaim->getBadge(), array($badgeClaim->getUser()));

                if (0 === $awardedBadge) {
                    throw new \Exception('No badge were awarded.');
                }
            }

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->remove($badgeClaim);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('success', $successMessage);
        } catch (\Exception $exception) {
            $this->get('session')->getFlashBag()->add('error', $errorMessage);
        }

        return $this->redirect(
            $this->generateUrl('claro_workspace_tool_badges', array('workspaceId' => $workspace->getId()))
        );
    }

    /**
     * @Route("/statistics", name="claro_workspace_tool_badges_statistics")
     * @ParamConverter(
     *     "workspace",
     *     class="ClarolineCoreBundle:Workspace\Workspace",
     *     options={"id" = "workspaceId"}
     * )
     * @Template()
     */
    public function statisticsAction(Request $request, Workspace $workspace)
    {
        $this->checkUserIsAllowed($workspace);

        /** @var \Claroline\CoreBundle\Repository\Badge\BadgeRepository $badgeRepository */
        $badgeRepository = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Badge\Badge');

        /** @var \Claroline\CoreBundle\Repository\Badge\UserBadgeRepository $userBadgeRepository */
        $userBadgeRepository = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Badge\UserBadge');

        $totalBadges       = $badgeRepository->countByWorkspace($workspace);
        $totalBadgeAwarded = $userBadgeRepository->countAwardedBadgeByWorkspace($workspace);

        $statistics = array(
            'totalBadges'          => $totalBadges,
            'totalAwarding'        => $userBadgeRepository->countAwardingByWorkspace($workspace),
            'totalBadgeAwarded'    => $totalBadgeAwarded,
            'totalBadgeNotAwarded' => $totalBadges - $totalBadgeAwarded
        );

        return array(
            'workspace'  => $workspace,
            'statistics' => $statistics
        );
    }

    private function checkUserIsAllowed(Workspace $workspace)
    {
        if (!$this->get('security.context')->isGranted('badges', $workspace)) {
            throw new AccessDeniedException();
        }
    }
}
