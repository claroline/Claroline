<?php

namespace Innova\CollecticielBundle\Controller;

use Innova\CollecticielBundle\Entity\Dropzone;
use Innova\CollecticielBundle\Event\Log\LogDropzoneConfigureEvent;
use Innova\CollecticielBundle\Event\Log\LogDropzoneManualStateChangedEvent;
use Innova\CollecticielBundle\Form\DropzoneAppreciationType;
use Innova\CollecticielBundle\Form\DropzoneCommonType;
use Innova\CollecticielBundle\Form\DropzoneCriteriaType;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class DropzoneController.
 *
 * @category  Controller
 *
 * @author    Innovalangues <contact@innovalangues.net>
 * @copyright 2016 Innovalangues
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 *
 * @link      http://innovalangues.net
 */
class DropzoneController extends DropzoneBaseController
{
    /**
     *@Route(
     *      "/{resourceId}/edit",
     *      name="innova_collecticiel_edit",
     *      requirements={"resourceId" = "\d+"}
     * )
     *@Route(
     *      "/{resourceId}/edit/common",
     *      name="innova_collecticiel_edit_common",
     *      requirements={"resourceId" = "\d+"}
     * )
     *@ParamConverter("user", options={
     *      "authenticatedUser" = true,
     *      "messageEnabled" = true,
     * "messageTranslationKey"
     * = "Participate in an evaluation requires authentication. Please login.",
     * "messageTranslationDomain" = "innova_collecticiel"
     * })
     *@ParamConverter("dropzone",
     * class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     *@Template()
     *
     * @param Dropzone $dropzone dropzone to handle
     * @param User     $user     user = teacher
     *
     *@return array (workspace, _resource,dropzone,form,
     *adminInnova,collecticielOpenOrNot)
     */
    public function editCommonAction(Dropzone $dropzone, $user)
    {
        $em = $this->getDoctrine()->getManager();
        $dropzoneVoter = $this->get('innova.manager.dropzone_voter');
        $dropzoneManager = $this->get('innova.manager.dropzone_manager');
        $translator = $this->get('translator');
        $platformConfigHandler
            = $this->get('claroline.config.platform_config_handler');

        $dropzoneVoter->isAllowToOpen($dropzone);
        $dropzoneVoter->isAllowToEdit($dropzone);
        $dropzoneManager = $this->get('innova.manager.dropzone_manager');

        if ($dropzone->getManualState() === 'notStarted') {
            $dropzone->setManualState('allowDrop');
            $em->persist($dropzone);
            $em->flush();
        }

        $form = $this->createForm(
            new DropzoneCommonType(), $dropzone,
            [
                'language' => $platformConfigHandler
                    ->getParameter('locale_language'),
                'date_format' => 'dd/MM/yyyy',
            ]
        );

        $request = $this->getRequest();

        if ($request->isMethod('POST')) {
            // see if manual planification option has changed.
            $oldManualPlanning = $dropzone->getManualPlanning();
            $oldManualPlanningOption = $dropzone->getManualState();

            $form->handleRequest($this->getRequest());

            // Mise à jour de la publication dans la table "claro_resource_node"
            $resourceId = $dropzone->getResourceNode()->getId();
            $dropzoneManager->updatePublished(
                $resourceId, $form->get('published')->getData()
            );

            $dropzone = $form->getData();

            // issues/300
            //const EVALUATION_TYPE = 'noEvaluation'
            if ($dropzone->getEvaluationType() !== Dropzone::EVALUATION_TYPE) {
                $dropzone->setEvaluation(1);
            }
            if ($dropzone->getEvaluationType() === Dropzone::EVALUATION_TYPE) {
                $dropzone->setEvaluation(0);
            }

            $form = $this->handleFormErrors($form, $dropzone);

            if ($dropzone->getEditionState() < 2) {
                $dropzone->setEditionState(2);
            }

            // handle events (delete if needed, create & update)
            $dropzone = $dropzoneManager->handleEvents($dropzone, $user);

            $manualStateChanged = false;
            $newManualState = null;
            if ($dropzone->getManualPlanning() === true) {
                if ($oldManualPlanning === false
                    || $oldManualPlanningOption !== $dropzone->getManualState()
                ) {
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
            if (count($dropzone->getDrops()) > 0) {
                if ($oldManualPlanningOption !== $dropzone->getManualState()) {
                    // send notification.
                    $usersIds = $dropzoneManager->getDropzoneUsersIds($dropzone);
                    $event = new LogDropzoneManualStateChangedEvent(
                        $dropzone, $dropzone->getManualState(), $usersIds
                    );
                    $this->get('event_dispatcher')->dispatch('log', $event);
                }
            }

            $em->persist($dropzone);
            $em->flush();

            // issues/262
            if ($dropzone->hasCriteria() !== false) {
                $request->getSession()->getFlashBag()->add(
                    'success',
                    $this->get('translator')
                        ->trans(
                            'The evaluation has been successfully saved',
                            [],
                            'icap_dropzone'
                        )
                );
            }

            $event = new LogDropzoneConfigureEvent($dropzone, $changeSet);
            $this->dispatch($event);

            $this->getRequest()->getSession()->getFlashBag()->add(
                'success',
                $translator->trans(
                    'The collecticiel has been successfully saved',
                    [],
                    'innova_collecticiel'
                )
            );
        }

        $adminInnova = false;
        if ($dropzoneVoter->checkEditRight($dropzone)
            && $this->get('security.token_storage')->getToken()->getUser()->getId() === $user->getId()
        ) {
            $adminInnova = true;
        }

        $collecticielOpenOrNot = $dropzoneManager->collecticielOpenOrNot($dropzone);

        return [
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'form' => $form->createView(),
            'adminInnova' => $adminInnova,
            'collecticielOpenOrNot' => $collecticielOpenOrNot,
        ];
    }

    /**
     *@Route(
     *      "/{resourceId}/edit",
     *      name="innova_collecticiel_edit",
     *      requirements={"resourceId" = "\d+"}
     * )
     *@Route(
     *      "/{resourceId}/edit/appreciation",
     *      name="innova_collecticiel_edit_appreciation",
     *      requirements={"resourceId" = "\d+"}
     * )
     *@ParamConverter("user", options={
     *      "authenticatedUser" = true,
     *      "messageEnabled" = true,
     *      "messageTranslationKey" =
     *       "Participate in an evaluation requires authentication. Please login.",
     *      "messageTranslationDomain" = "innova_collecticiel"
     * })
     *@ParamConverter("dropzone",
     * class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     *@Template()
     *
     * @param Dropzone $dropzone dropzone to handle
     * @param User     $user     user = teacher
     *
     *@return array (workspace, _resource,dropzone,form,
     *adminInnova,collecticielOpenOrNot)
     */
    public function editAppreciationAction(Dropzone $dropzone, $user)
    {
        $em = $this->getDoctrine()->getManager();
        $dropzoneVoter = $this->get('innova.manager.dropzone_voter');
        $dropzoneManager = $this->get('innova.manager.dropzone_manager');
        $translator = $this->get('translator');

        $dropzoneVoter->isAllowToOpen($dropzone);
        $dropzoneVoter->isAllowToEdit($dropzone);
        $dropzoneManager = $this->get('innova.manager.dropzone_manager');
        $gradingScaleManager = $this->get('innova.manager.gradingscale_manager');
        $gradingCriteriaManager
            = $this->get('innova.manager.gradingcriteria_manager');

        if ($dropzone->getManualState() === 'notStarted') {
            $dropzone->setManualState('allowDrop');
            $em->persist($dropzone);
            $em->flush();
        }

        $form = $this->createForm(new DropzoneAppreciationType(), $dropzone);

        if ($this->getRequest()->isMethod('POST')) {
            $tab = $this->getRequest()
                ->request->get('innova_collecticiel_appreciation_form');

            $gradingScaleManager
                ->manageGradingScales(
                    $tab[
                    'gradingScales'
                    ],
                    $dropzone
                );

            $gradingCriteriaManager
                ->manageGradingCriterias($tab['gradingCriterias'], $dropzone);

            // see if manual planification option has changed.
            $oldManualPlanning = $dropzone->getManualPlanning();
            $oldManualPlanningOption = $dropzone->getManualState();

            if ($dropzone->getEditionState() < 2) {
                $dropzone->setEditionState(2);
            }

            // issues/502
            $dropzone->setEvaluationType('ratingScale');
            $dropzone->setEvaluation(1);

            // handle events (delete if needed, create & update)
            $dropzone = $dropzoneManager->handleEvents($dropzone, $user);

            $manualStateChanged = false;
            $newManualState = null;
            if ($dropzone->getManualPlanning() === true) {
                if ($oldManualPlanning === false
                    || $oldManualPlanningOption !== $dropzone->getManualState()
                ) {
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
            if (count($dropzone->getDrops()) > 0) {
                if ($oldManualPlanningOption !== $dropzone->getManualState()) {
                    // send notification.
                    $usersIds = $dropzoneManager->getDropzoneUsersIds($dropzone);
                    $event = new LogDropzoneManualStateChangedEvent(
                        $dropzone, $dropzone->getManualState(), $usersIds
                    );
                    $this->get('event_dispatcher')->dispatch('log', $event);
                }
            }

            $event = new LogDropzoneConfigureEvent($dropzone, $changeSet);
            $this->dispatch($event);

            $this->getRequest()->getSession()->getFlashBag()->add(
                'success',
                $translator->trans(
                    'The collecticiel has been successfully saved',
                    [],
                    'innova_collecticiel'
                )
            );
            // redirect to main dropzone settings view
            return $this->redirect(
                $this->generateUrl(
                    'innova_collecticiel_edit_common',
                    ['resourceId' => $dropzone->getId()]
                )
            );
        }

        $adminInnova = false;
        if ($dropzoneVoter->checkEditRight($dropzone)
            && $this->get('security.token_storage')->getToken()->getUser()->getId() === $user->getId()
        ) {
            $adminInnova = true;
        }

        $collecticielOpenOrNot = $dropzoneManager->collecticielOpenOrNot($dropzone);

        return [
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'form' => $form->createView(),
            'adminInnova' => $adminInnova,
            'maximumNotation' => $dropzone->getMaximumNotation(),
            'collecticielOpenOrNot' => $collecticielOpenOrNot,
        ];
    }

    /**
     *@Route(
     *      "/{resourceId}/edit/criteria",
     *      name="innova_collecticiel_edit_criteria",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     *@Route(
     *      "/{resourceId}/edit/criteria",
     *      name="innova_collecticiel_edit_criteria_innova",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     *
     *@Route(
     *      "/{resourceId}/edit/criteria/{page}",
     *      name="innova_collecticiel_edit_criteria_paginated",
     *      requirements={"resourceId" = "\d+", "page" = "\d+"},
     *      defaults={"page" = 1}
     * )
     *@ParamConverter("dropzone",
     * class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     *@Template()
     *
     * @param Dropzone $dropzone dropzone to handle
     * @param int      $page     page to display
     *
     *@return array (workspace, _resource,dropzone,pager,form,
     *nbCorrection,add_criteria_after,
     *adminInnova,collecticielOpenOrNot)
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
                        [
                            'resourceId' => $dropzone->getId(),
                            'page' => $pager->getNbPages(),
                        ]
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
                $add_criteria_after = $this->getRequest()
                    ->request->get('addCriteria') === 'add-criterion' ? true : false;

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

                if ($form->get('recalculateGrades')->getData() === 1) {
                    $this->get('innova.manager.dropzone_manager')
                        ->recalculateScoreByDropzone($dropzone);
                    $this->getRequest()->getSession()->getFlashBag()->add(
                        'success',
                        $this->get('translator')
                            ->trans(
                                'Grades were recalculated',
                                [],
                                'innova_collecticiel'
                            )
                    );
                }

                $event = new LogDropzoneConfigureEvent($dropzone, $changeSet);
                $this->dispatch($event);

                if ($dropzone->hasCriteria() === false) {
                    $this->getRequest()->getSession()->getFlashBag()->add(
                        'warning',
                        $this->get('translator')
                            ->trans(
                                'Warning your peer review offers no criteria
                                 on which to base correct copies',
                                [],
                                'innova_collecticiel'
                            )
                    );
                }
                if ($add_criteria_after) {
                    return new JsonResponse(['success' => true]);
                }

                $goBack = $form->get('goBack')->getData();
                if ($goBack === 0) {
                    $this->getRequest()->getSession()->getFlashBag()->add(
                        'success',
                        $this->get('translator')
                            ->trans(
                                'The collecticiel has been successfully saved',
                                [],
                                'innova_collecticiel'
                            )
                    );
                } else {
                    return $this->redirect(
                        $this->generateUrl(
                            'innova_collecticiel_edit_common',
                            [
                                'resourceId' => $dropzone->getId(),
                            ]
                        )
                    );
                }
            }
        }

        $dropzoneVoter = $this->get('innova.manager.dropzone_voter');
        $dropzoneManager = $this->get('innova.manager.dropzone_manager');

        $collecticielOpenOrNot = $dropzoneManager->collecticielOpenOrNot($dropzone);
        $adminInnova = $dropzoneVoter->checkEditRight($dropzone);

        return [
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'pager' => $pager,
            'form' => $form->createView(),
            'nbCorrection' => $nbCorrection,
            'add_criteria_after' => $add_criteria_after,
            'adminInnova' => $adminInnova,
            'collecticielOpenOrNot' => $collecticielOpenOrNot,
        ];
    }

    /**
     *@Route(
     *      "/{resourceId}/open",
     *      name="innova_collecticiel_open",
     *      requirements={"resourceId" = "\d+"}
     * )
     *@ParamConverter("dropzone",
     * class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     *@ParamConverter("user", options={
     *      "authenticatedUser" = true,
     *      "messageEnabled" = true,
     *      "messageTranslationKey" =
     * "Participate in an evaluation requires authentication. Please login.",
     *      "messageTranslationDomain" = "innova_collecticiel"
     * })
     *@Template()
     *
     * @param Dropzone $dropzone dropzone to handle
     * @param User     $user     user = teacher
     *
     *@return array (workspace, _resource,dropzone,drop,nbCorrections,
     *hasCopyToCorrect,hasUnfinishedCorrection,
     *dropzoneProgress,PeerReviewEndCase)
     */
    public function openAction(Dropzone $dropzone, $user)
    {
        //Participant view for a dropzone
        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);

        $em = $this->getDoctrine()->getManager();
        $dropRepo = $em->getRepository('InnovaCollecticielBundle:Drop');
        $drop = $dropRepo->findOneBy(['dropzone' => $dropzone, 'user' => $user]);
        $dropzoneManager = $this->get('innova.manager.dropzone_manager');
        // check if endAllowDrop is past and close all unvalidated
        // drops if autoclose options is activated.
        if ($dropzone->getAutoCloseState() === Dropzone::AUTO_CLOSED_STATE_WAITING) {
            $dropzoneManager->closeDropzoneOpenedDrops($dropzone);
        }

        $nbCorrections = $em
            ->getRepository('InnovaCollecticielBundle:Correction')
            ->countFinished($dropzone, $user);
        $hasCopyToCorrect = $em
            ->getRepository('InnovaCollecticielBundle:Drop')
            ->hasCopyToCorrect($dropzone, $user);

        $hasUnfinishedCorrection
            = $em->getRepository('InnovaCollecticielBundle:Correction')
                ->getNotFinished($dropzone, $user) !== null;

        // get progression of the evaluation
        // ( current state, all states available and needed infos to the view).
        $dropzoneProgress = $dropzoneManager
            ->getDrozponeProgress($dropzone, $drop, $nbCorrections);

        $PeerReviewEndCase = $dropzoneManager
            ->isPeerReviewEndedOrManualStateFinished($dropzone, $nbCorrections);

        return [
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'drop' => $drop,
            'nbCorrections' => $nbCorrections,
            'hasCopyToCorrect' => $hasCopyToCorrect,
            'hasUnfinishedCorrection' => $hasUnfinishedCorrection,
            'dropzoneProgress' => $dropzoneProgress,
            'PeerReviewEndCase' => $PeerReviewEndCase,
        ];
    }

    /**
     *  Test to detect the incorrect input.
     *
     * @param Form     $form     form to control
     * @param Dropzone $dropzone dropzone to update
     *
     * @return form
     */
    public function handleFormErrors($form, Dropzone $dropzone)
    {
        if (!$dropzone->getAllowWorkspaceResource()
            && !$dropzone->getAllowUpload()
            && !$dropzone->getAllowUrl()
            && !$dropzone->getAllowRichText()
        ) {
            $form->get('allowWorkspaceResource')
                ->addError(new FormError('Choose at least one type of document'));
            $form->get('allowUpload')
                ->addError(new FormError('Choose at least one type of document'));
            $form->get('allowUrl')
                ->addError(new FormError('Choose at least one type of document'));
            $form->get('allowRichText')
                ->addError(new FormError('Choose at least one type of document'));
        }

        if (!$dropzone->getManualPlanning()) {
            if ($dropzone->getStartAllowDrop() === null) {
                $form->get('startAllowDrop')
                    ->addError(new FormError('Choose a date'));
            }
            if ($dropzone->getEndAllowDrop() === null) {
                $form->get('endAllowDrop')->addError(new FormError('Choose a date'));
            }

            if ($dropzone->getStartAllowDrop() !== null
                && $dropzone->getEndAllowDrop() !== null
            ) {
                if ($dropzone->getStartAllowDrop()->getTimestamp()
                    > $dropzone->getEndAllowDrop()->getTimestamp()
                ) {
                    $form->get('startAllowDrop')
                        ->addError(new FormError('Must be before end allow drop'));
                    $form->get('endAllowDrop')
                        ->addError(new FormError('Must be after start allow drop'));
                }
            }
        }

        return $form;
    }

    /**
     *@Route(
     *      "/add/update/dropzone",
     *      name="innova_collecticiel_update_dropzone",
     *      options={"expose"=true}
     * )
     *@Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAjaxDropzoneAction()
    {
        // Dropzone ID
        $dropzoneId = $this->get('request')->query->get('dropzoneId');

        // datas from JS
        $instruction = $this->get('request')->query->get('instruction');

        $allowWorkspaceResource
            = $this->get('request')->query->get('allowWorkspaceResource');
        $allowUpload = $this->get('request')->query->get('allowUpload');
        $allowUrl = $this->get('request')->query->get('allowUrl');
        $allowRichText = $this->get('request')->query->get('allowRichText');

        $manualPlanning = $this->get('request')->query->get('manualPlanning');
        $manualState = $this->get('request')->query->get('manualState');

        $startAllowDrop_date
            = $this->get('request')->query->get('startAllowDrop_date');
        $startAllowDrop_time
            = $this->get('request')->query->get('startAllowDrop_time');
        $endAllowDrop_date = $this->get('request')->query->get('endAllowDrop_date');
        $endAllowDrop_time = $this->get('request')->query->get('endAllowDrop_time');

        $published = $this->get('request')->query->get('published');
        $returnReceipt = $this->get('request')->query->get('returnReceipt');
        $picture = $this->get('request')->query->get('picture');
        $username = $this->get('request')->query->get('username');

        // Database dropzone
        $em = $this->getDoctrine()->getManager();
        $dropzone = $em->getRepository('InnovaCollecticielBundle:Dropzone')
            ->find($dropzoneId);

        // Updating dropzone
        $dropzone->setInstruction($instruction);

        $dropzone->setAllowWorkspaceResource($allowWorkspaceResource);
        $dropzone->setAllowUpload($allowUpload);
        $dropzone->setAllowUrl($allowUrl);
        $dropzone->setAllowRichText($allowRichText);

        // Mise à jour de la publication dans la table "claro_resource_node"
        $dropzoneManager = $this->get('innova.manager.dropzone_manager');
        $resourceId = $dropzone->getResourceNode()->getId();
        $dropzoneManager->updatePublished($resourceId, $published);

        $dropzone->setReturnReceipt($returnReceipt);
        $dropzone->setPicture($picture);
        $dropzone->setUsername($username);

        // Manage dropzone type and dates
        if ($manualPlanning === 1) {
            $dropzone->setManualPlanning($manualPlanning);
            $dropzone->setManualState($manualState);
        } else {
            $dropzone->setManualPlanning($manualPlanning);
            if ($startAllowDrop_date !== '0') {
                $formatDate = explode('/', $startAllowDrop_date);
                $startAllowDrop = $formatDate[2].'-'.$formatDate[1].'-'
                    .$formatDate[0].' '.$startAllowDrop_time.':00';
                $dropzone->setStartAllowDrop(
                    \DateTime::createFromFormat('Y-m-d H:i:s', $startAllowDrop)
                );
            }
            if ($endAllowDrop_date !== '0') {
                $formatDate = explode('/', $endAllowDrop_date);
                $endAllowDrop = $formatDate[2].'-'.$formatDate[1].'-'
                    .$formatDate[0].' '.$endAllowDrop_time.':00';
                $dropzone->setEndAllowDrop(
                    \DateTime::createFromFormat('Y-m-d H:i:s', $endAllowDrop)
                );
            }
        }

        // Database updating dropzone
        $em->persist($dropzone);
        $em->flush();

        // Redirect
        $url = $this->generateUrl(
            'innova_collecticiel_edit_appreciation',
            ['resourceId' => $dropzone->getId()]
        );

        return new JsonResponse(['link' => $url]);
    }
}
