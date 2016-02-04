<?php
namespace Innova\CollecticielBundle\Controller;

use Innova\CollecticielBundle\Entity\Dropzone;
use Innova\CollecticielBundle\Event\Log\LogDropzoneConfigureEvent;
use Innova\CollecticielBundle\Event\Log\LogDropzoneManualStateChangedEvent;
use Innova\CollecticielBundle\Form\DropzoneCommonType;
use Innova\CollecticielBundle\Form\DropzoneCriteriaType;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Claroline\AgendaBundle\Entity\Event;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DropzoneController extends DropzoneBaseController
{
    /**
     * @Route(
     *      "/{resourceId}/edit",
     *      name="innova_collecticiel_edit",
     *      requirements={"resourceId" = "\d+"}
     * )
     * @Route(
     *      "/{resourceId}/edit/common",
     *      name="innova_collecticiel_edit_common",
     *      requirements={"resourceId" = "\d+"}
     * )
     * @ParamConverter("user", options={
     *      "authenticatedUser" = true,
     *      "messageEnabled" = true,
     *      "messageTranslationKey" = "Participate in an evaluation requires authentication. Please login.",
     *      "messageTranslationDomain" = "innova_collecticiel"
     * })
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     *
     * User is needed for Agenda Event
     */
    public function editCommonAction(Dropzone $dropzone, $user)
    {
        $em                     = $this->getDoctrine()->getManager();
        $dropzoneVoter          = $this->get('innova.manager.dropzone_voter');
        $dropzoneManager        = $this->get('innova.manager.dropzone_manager');
        $translator             = $this->get('translator');
        $platformConfigHandler  = $this->get('claroline.config.platform_config_handler');

        $dropzoneVoter->isAllowToOpen($dropzone);
        $dropzoneVoter->isAllowToEdit($dropzone);
        $dropzoneManager = $this->get('innova.manager.dropzone_manager');

        $form = $this->createForm(
            new DropzoneCommonType(), $dropzone,
            array('language' => $platformConfigHandler->getParameter('locale_language'), 'date_format' => 'dd/MM/yyyy')
        );

        if ($this->getRequest()->isMethod('POST')) {
            // see if manual planification option has changed.
            $oldManualPlanning = $dropzone->getManualPlanning();
            $oldManualPlanningOption = $dropzone->getManualState();

            $form->handleRequest($this->getRequest());

            // Mise à jour de la publication dans la table "claro_resource_node"
            $resourceId = $dropzone->getResourceNode()->getId();
            $resourceNodes = $dropzoneManager->updatePublished($resourceId, $form->get('published')->getData());

            $dropzone = $form->getData();
            $form = $this->handleFormErrors($form, $dropzone);

            if ($dropzone->getEditionState() < 2) {
                $dropzone->setEditionState(2);
            }

            // handle events (delete if needed, create & update)
            $dropzone = $dropzoneManager->handleEvents($dropzone, $user);

            $manualStateChanged = false;
            $newManualState = null;
            if ($dropzone->getManualPlanning() === true) {
                if ($oldManualPlanning === false || $oldManualPlanningOption != $dropzone->getManualState()) {
                    $manualStateChanged = true;
                    $newManualState = $dropzone->getManualState();
                }
            }

            $unitOfWork = $em->getUnitOfWork();
            $unitOfWork->computeChangeSets();
            $changeSet = $unitOfWork->getEntityChangeSet($dropzone);

            $em = $this->getDoctrine()->getManager();

            // InnovaERV : ici, on a changé l'état du collecticiel.
            // InnovaERV : j'ajoute une notification.
            // InnovaERV : #171 Bug : lors de la création d'un collecticiel et de la notification
            if (count($dropzone->getDrops()) > 0)
            {
                if ($oldManualPlanningOption != $dropzone->getManualState())
                {
                    // send notification.
                    $usersIds = $dropzoneManager->getDropzoneUsersIds($dropzone);
                    $event = new LogDropzoneManualStateChangedEvent($dropzone, $dropzone->getManualState(), $usersIds);
                    $this->get('event_dispatcher')->dispatch('log', $event);
                }
            }

            $em->persist($dropzone);
            $em->flush();

            // check if manual state has changed
//            if ($manualStateChanged) {
//                // send notification.
//                $usersIds = $dropzoneManager->getDropzoneUsersIds($dropzone);
//                $event = new LogDropzoneManualStateChangedEvent($dropzone, $newManualState, $usersIds);
//                $this->get('event_dispatcher')->dispatch('log', $event);
//            }

            $event = new LogDropzoneConfigureEvent($dropzone, $changeSet);
            $this->dispatch($event);

            $this->getRequest()->getSession()->getFlashBag()->add('success', $translator->trans('The collecticiel has been successfully saved', array(), 'innova_collecticiel'));
        }

        $adminInnova = false;
        if ($dropzoneVoter->checkEditRight($dropzone)
        && $this->get('security.token_storage')->getToken()->getUser()->getId() == $user->getId()) {
            $adminInnova = true;
        }
    
        $collecticielOpenOrNot = $dropzoneManager->collecticielOpenOrNot($dropzone);

        return array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'form' => $form->createView(),
            'adminInnova' => $adminInnova,
            'collecticielOpenOrNot' => $collecticielOpenOrNot
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/edit/criteria",
     *      name="innova_collecticiel_edit_criteria",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @Route(
     *      "/{resourceId}/edit/criteria",
     *      name="innova_collecticiel_edit_criteria_innova",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     *
     * @Route(
     *      "/{resourceId}/edit/criteria/{page}",
     *      name="innova_collecticiel_edit_criteria_paginated",
     *      requirements={"resourceId" = "\d+", "page" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function editCriteriaAction(Dropzone $dropzone, $page)
    {
        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('innova.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('InnovaCollecticielBundle:Criterion');
        $query = $repository
            ->createQueryBuilder('criterion')
            ->andWhere('criterion.dropzone = :dropzone')
            ->setParameter('dropzone', $dropzone)
            ->orderBy('criterion.id', 'ASC');

        $adapter = new DoctrineORMAdapter($query);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(DropzoneBaseController::CRITERION_PER_PAGE);
        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            if ($page > 0) {
                return $this->redirect(
                    $this->generateUrl(
                        'innova_collecticiel_edit_criteria_paginated',
                        array(
                            'resourceId' => $dropzone->getId(),
                            'page' => $pager->getNbPages(),
                        )
                    )
                );
            } else {
                throw new NotFoundHttpException();
            }
        }

        $nbCorrection = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('InnovaCollecticielBundle:Correction')
            ->countByDropzone($dropzone->getId());

        $form = $this->createForm(new DropzoneCriteriaType(), $dropzone);
        $add_criteria_after = false;
        if ($this->getRequest()->isMethod('POST')) {
            $form->handleRequest($this->getRequest());

            if ($form->isValid()) {
                $add_criteria_after = $this->getRequest()->request->get('addCriteria') == 'add-criterion' ? true : false;

                $dropzone = $form->getData();
                if ($dropzone->getEditionState() < 3) {
                    $dropzone->setEditionState(3);
                }

                $em = $this->getDoctrine()->getManager();
                $unitOfWork = $em->getUnitOfWork();
                $unitOfWork->computeChangeSets();
                $changeSet = $unitOfWork->getEntityChangeSet($dropzone);

                $em->persist($dropzone);
                $em->flush();

                if ($form->get('recalculateGrades')->getData() == 1) {
                    $this->get('innova.manager.dropzone_manager')->recalculateScoreByDropzone($dropzone);
                    $this->getRequest()->getSession()->getFlashBag()->add(
                        'success',
                        $this->get('translator')->trans('Grades were recalculated', array(), 'innova_collecticiel')
                    );
                }

                $event = new LogDropzoneConfigureEvent($dropzone, $changeSet);
                $this->dispatch($event);

                if ($dropzone->hasCriteria() === false) {
                    $this->getRequest()->getSession()->getFlashBag()->add(
                        'warning',
                        $this->get('translator')->trans('Warning your peer review offers no criteria on which to base correct copies', array(), 'innova_collecticiel')
                    );
                }
                if ($add_criteria_after) {
                    return new JsonResponse(array('success' => true));
                    //$this->generateUrl('innova_collecticiel_edit_add_criterion',array('resourceId'=>$dropzone->getId(),'page'=>$page));
                }

                $goBack = $form->get('goBack')->getData();
                if ($goBack == 0) {
                    $this->getRequest()->getSession()->getFlashBag()->add(
                        'success',
                        $this->get('translator')->trans('The collecticiel has been successfully saved', array(), 'innova_collecticiel')
                    );
                } else {
                    return $this->redirect(
                        $this->generateUrl(
                            'innova_collecticiel_edit_common',
                            array(
                                'resourceId' => $dropzone->getId(),
                            )
                        )
                    );
                }
            }
        }

        $adminInnova = $dropzoneVoter->checkEditRight($dropzone);
    /*    if ($this->get('security.context')->isGranted('ROLE_ADMIN' === true)) {
            $adminInnova = true;
        }*/

        return array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'pager' => $pager,
            'form' => $form->createView(),
            'nbCorrection' => $nbCorrection,
            'add_criteria_after' => $add_criteria_after,
            'adminInnova' => $adminInnova,
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/open",
     *      name="innova_collecticiel_open",
     *      requirements={"resourceId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={
     *      "authenticatedUser" = true,
     *      "messageEnabled" = true,
     *      "messageTranslationKey" = "Participate in an evaluation requires authentication. Please login.",
     *      "messageTranslationDomain" = "innova_collecticiel"
     * })
     * @Template()
     */
    public function openAction(Dropzone $dropzone, $user)
    {
        //Participant view for a dropzone
        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);

        $em = $this->getDoctrine()->getManager();
        $dropRepo = $em->getRepository('InnovaCollecticielBundle:Drop');
        $drop = $dropRepo->findOneBy(array('dropzone' => $dropzone, 'user' => $user));
        $dropzoneManager = $this->get('innova.manager.dropzone_manager');
        // check if endAllowDrop is past and close all unvalidated
        // drops if autoclose options is activated.
        if ($dropzone->getAutoCloseState() == Dropzone::AUTO_CLOSED_STATE_WAITING) {
            $dropzoneManager->closeDropzoneOpenedDrops($dropzone);
        }

        $nbCorrections = $em
            ->getRepository('InnovaCollecticielBundle:Correction')
            ->countFinished($dropzone, $user);
        $hasCopyToCorrect = $em
            ->getRepository('InnovaCollecticielBundle:Drop')
            ->hasCopyToCorrect($dropzone, $user);

        $hasUnfinishedCorrection =
        $em->getRepository('InnovaCollecticielBundle:Correction')->getNotFinished($dropzone, $user) != null;

        // get progression of the evaluation ( current state, all states available and needed infos to the view).
        $dropzoneProgress = $dropzoneManager->getDrozponeProgress($dropzone, $drop, $nbCorrections);

        $PeerReviewEndCase = $dropzoneManager->isPeerReviewEndedOrManualStateFinished($dropzone, $nbCorrections);

        return array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'drop' => $drop,
            'nbCorrections' => $nbCorrections,
            'hasCopyToCorrect' => $hasCopyToCorrect,
            'hasUnfinishedCorrection' => $hasUnfinishedCorrection,
            'dropzoneProgress' => $dropzoneProgress,
            'PeerReviewEndCase' => $PeerReviewEndCase,
        );
    }

    private function handleFormErrors($form, Dropzone $dropzone)
    {
        if (
            !$dropzone->getAllowWorkspaceResource()
            && !$dropzone->getAllowUpload()
            && !$dropzone->getAllowUrl()
            && !$dropzone->getAllowRichText()
            ) {
            $form->get('allowWorkspaceResource')->addError(new FormError('Choose at least one type of document'));
            $form->get('allowUpload')->addError(new FormError('Choose at least one type of document'));
            $form->get('allowUrl')->addError(new FormError('Choose at least one type of document'));
            $form->get('allowRichText')->addError(new FormError('Choose at least one type of document'));
        }

        if (!$dropzone->getManualPlanning()) {
            if ($dropzone->getStartAllowDrop() == null) {
                $form->get('startAllowDrop')->addError(new FormError('Choose a date'));
            }
            if ($dropzone->getEndAllowDrop() == null) {
                $form->get('endAllowDrop')->addError(new FormError('Choose a date'));
            }

            if ($dropzone->getStartAllowDrop() != null && $dropzone->getEndAllowDrop() != null) {
                if ($dropzone->getStartAllowDrop()->getTimestamp() > $dropzone->getEndAllowDrop()->getTimestamp()) {
                    $form->get('startAllowDrop')->addError(new FormError('Must be before end allow drop'));
                    $form->get('endAllowDrop')->addError(new FormError('Must be after start allow drop'));
                }
            }
        }

        return $form;
    }
}
