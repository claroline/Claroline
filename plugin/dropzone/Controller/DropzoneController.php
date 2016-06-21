<?php

namespace Icap\DropzoneBundle\Controller;

use Icap\DropzoneBundle\Entity\Dropzone;
use Icap\DropzoneBundle\Event\Log\LogDropzoneConfigureEvent;
use Icap\DropzoneBundle\Event\Log\LogDropzoneManualStateChangedEvent;
use Icap\DropzoneBundle\Form\DropsDownloadBetweenDatesType;
use Icap\DropzoneBundle\Form\DropzoneCommonType;
use Icap\DropzoneBundle\Form\DropzoneCriteriaType;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Claroline\AgendaBundle\Entity\Event;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormError;
use DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DropzoneController extends DropzoneBaseController
{
    /**
     * @Route(
     *      "/{resourceId}/edit",
     *      name="icap_dropzone_edit",
     *      requirements={"resourceId" = "\d+"}
     * )
     * @Route(
     *      "/{resourceId}/edit/common",
     *      name="icap_dropzone_edit_common",
     *      requirements={"resourceId" = "\d+"}
     * )
     * @ParamConverter("user", options={
     *      "authenticatedUser" = true,
     *      "messageEnabled" = true,
     *      "messageTranslationKey" = "Participate in an evaluation requires authentication. Please login.",
     *      "messageTranslationDomain" = "icap_dropzone"
     * })
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     *
     * User is needed for Agenda Event
     */
    public function editCommonAction(Request $request, Dropzone $dropzone, $user)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);
        $platformConfigHandler = $this->get('claroline.config.platform_config_handler');
        $dateFormat = $this->get('translator')->trans('date_form_format', array(), 'platform');
        $lang = $platformConfigHandler->getParameter('locale_language');
        $form = $this->createForm(
            new DropzoneCommonType(),
            $dropzone,
            array('language' => $lang, 'date_format' => $dateFormat)
        );

        if ($request->isMethod('POST')) {
            // see if manual plannification option has changed.
            $oldManualPlanning = $dropzone->getManualPlanning();
            $oldManualPlanningOption = $dropzone->getManualState();

            $oldEndDropDate = $dropzone->getEndAllowDrop();

            $form->handleRequest($request);

            /** @var Dropzone $dropzone */
            $dropzone = $form->getData();

            if (!$dropzone->getPeerReview() && $dropzone->getManualState() == 'peerReview') {
                $dropzone->setManualState('notStarted');
            }
            if ($dropzone->getEditionState() < 2) {
                $dropzone->setEditionState(2);
            }

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
                $form_array = $request->request->get('icap_dropzone_common_form');

                if (is_array($form_array)) {
                    // reconstruction of datetimes.

                    if (array_key_exists('startAllowDrop', $form_array)) {
                        $dateStr = implode(' ', $form_array['startAllowDrop']);
                        if ($startDrop = $this->validateDate($dateStr, $dateFormat)) {
                            $dropzone->setStartAllowDrop($startDrop);
                        }
                    }

                    if (array_key_exists('endAllowDrop', $form_array)) {
                        $dateStr = implode(' ', $form_array['endAllowDrop']);
                        if ($endDrop = $this->validateDate($dateStr, $dateFormat)) {
                            $dropzone->setEndAllowDrop($endDrop);
                        }
                    }

                    if (array_key_exists('startReview', $form_array)) {
                        $dateStr = implode(' ', $form_array['startReview']);
                        if ($startReview = $this->validateDate($dateStr, $dateFormat)) {
                            $dropzone->setStartReview($startReview);
                        }
                    }

                    if (array_key_exists('endReview', $form_array)) {
                        $dateStr = implode(' ', $form_array['endReview']);
                        if ($endReview = $this->validateDate($dateStr, $dateFormat)) {
                            $dropzone->setEndReview($endReview);
                        }
                    }

                    $AgendaManager = $this->get('claroline.manager.agenda_manager');
                    $workspace = $dropzone->getResourceNode()->getWorkspace();
                    //Set the Agenda Drop Events.
                    if ($dropzone->getStartAllowDrop() != null && $dropzone->getEndAllowDrop() != null) {

                        //if event already exist
                        if ($dropzone->getEventDrop() != null) {

                            // update event
                            $eventDrop = $dropzone->getEventDrop();
                            $eventDrop->setStart($dropzone->getStartAllowDrop()->getTimeStamp());
                            $eventDrop->setEnd($dropzone->getEndAllowDrop()->getTimeStamp());

                            $AgendaManager->updateEvent($eventDrop);
                        } else {
                            //if event doesn't exist
                            // create event
                            $eventDrop = $this->createAgendaEventDrop($dropzone->getStartAllowDrop(), $dropzone->getEndAllowDrop(), $user, $dropzone, 'drop');
                            // event creation + link to workspace
                            $AgendaManager->addEvent($eventDrop, $workspace);
                            // link btween the event and the dropzone
                            $dropzone->setEventDrop($eventDrop);
                        }
                    }

                    //Set the Agenda Review Events.
                    if ($dropzone->getStartReview() != null && $dropzone->getEndReview() != null) {

                        // if event is already linked.
                        if ($dropzone->getEventCorrection() != null) {
                            //update event
                            $eventCorrection = $dropzone->getEventCorrection();
                            $eventCorrection->setStart($dropzone->getStartReview());
                            $eventCorrection->setEnd($dropzone->getEndReview());
                            $AgendaManager->updateEvent($eventCorrection);
                        } else {
                            //create event
                            $eventReview = $this->createAgendaEventDrop($dropzone->getStartReview(), $dropzone->getEndReview(), $user, $dropzone, 'correction');
                            $AgendaManager->addEvent($eventReview, $workspace);
                            $dropzone->setEventCorrection($eventReview);
                        }
                    }

                    if ($dropzone->getStartAllowDrop() == null) {
                        $form->get('startAllowDrop')->addError(new FormError('Choose a date'));
                    }
                    if ($dropzone->getEndAllowDrop() == null) {
                        $form->get('endAllowDrop')->addError(new FormError('Choose a date'));
                    }
                    if ($dropzone->getPeerReview() && $dropzone->getEndReview() == null) {
                        $form->get('endReview')->addError(new FormError('Choose a date'));
                    }
                    if ($dropzone->getStartAllowDrop() != null && $dropzone->getEndAllowDrop() != null) {
                        if ($dropzone->getStartAllowDrop()->getTimestamp() > $dropzone->getEndAllowDrop()->getTimestamp()) {
                            $form->get('startAllowDrop')->addError(new FormError('Must be before end allow drop'));
                            $form->get('endAllowDrop')->addError(new FormError('Must be after start allow drop'));
                        }
                    }
                    if ($dropzone->getStartReview() != null && $dropzone->getEndReview() != null) {
                        if ($dropzone->getStartReview()->getTimestamp() > $dropzone->getEndReview()->getTimestamp()) {
                            $form->get('startReview')->addError(new FormError('Must be before end peer review'));
                            $form->get('endReview')->addError(new FormError('Must be after start peer review'));
                        }
                    }
                    if ($dropzone->getStartAllowDrop() != null && $dropzone->getStartReview() != null) {
                        if ($dropzone->getStartAllowDrop()->getTimestamp() > $dropzone->getStartReview()->getTimestamp()) {
                            $form->get('startReview')->addError(new FormError('Must be after start allow drop'));
                            $form->get('startAllowDrop')->addError(new FormError('Must be before start peer review'));
                        }
                    }
                    if ($dropzone->getEndAllowDrop() != null && $dropzone->getEndReview() != null) {
                        if ($dropzone->getEndAllowDrop()->getTimestamp() > $dropzone->getEndReview()->getTimestamp()) {
                            $form->get('endReview')->addError(new FormError('Must be after end allow drop'));
                            $form->get('endAllowDrop')->addError(new FormError('Must be before end peer review'));
                        }
                    }
                } else {
                    //$form_array is not an array
                    $form->get('ManualPlanning')->addError(new FormError(''));
                }
            } else {
                // if manual mode, we delete agenda events related to
                $AgendaManager = $this->get('claroline.manager.agenda_manager');

                if ($dropzone->getEventDrop() != null) {
                    $event = $dropzone->getEventDrop();
                    $AgendaManager->deleteEvent($event);
                    $dropzone->setEventDrop(null);
                }

                if ($dropzone->getEventCorrection() != null) {
                    $event = $dropzone->getEventCorrection();
                    $AgendaManager->deleteEvent($event);
                    $dropzone->setEventCorrection(null);
                }
            }

            if ($form->isValid()) {
                //getting the dropzoneManager
                $dropzoneManager = $this->get('icap.manager.dropzone_manager');

                if ($dropzone->getPeerReview() != true) {
                    $dropzone->setExpectedTotalCorrection(1);
                    if ($dropzone->getManualState() == 'peerReview') {
                        $dropzone->setManualState('notStarted');
                    }
                }

                $manualStateChanged = false;
                $newManualState = null;
                if ($dropzone->getManualPlanning() == true) {
                    if ($oldManualPlanning == false || $oldManualPlanningOption != $dropzone->getManualState()) {
                        $manualStateChanged = true;
                        $newManualState = $dropzone->getManualState();
                    }
                    // option auto Close unterminated drops
                    if ($form->get('autoCloseForManualStates')->getData() == 1) {
                        $dropzoneManager->closeDropzoneOpenedDrops($dropzone, true);
                    }
                } else {
                    if ($oldEndDropDate != $dropzone->getEndAllowDrop()) {
                        $dropzone->setAutoCloseState(Dropzone::AUTO_CLOSED_STATE_WAITING);
                    }
                }

                $em = $this->getDoctrine()->getManager();

                $unitOfWork = $em->getUnitOfWork();
                $unitOfWork->computeChangeSets();
                $changeSet = $unitOfWork->getEntityChangeSet($dropzone);

                $em = $this->getDoctrine()->getManager();
                $em->persist($dropzone);
                $em->flush();

                // check if manual state has changed
                if ($manualStateChanged) {
                    // send notification.

                    $usersIds = $dropzoneManager->getDropzoneUsersIds($dropzone);
                    $event = new LogDropzoneManualStateChangedEvent($dropzone, $newManualState, $usersIds);
                    $this->get('event_dispatcher')->dispatch('log', $event);
                }
                $event = new LogDropzoneConfigureEvent($dropzone, $changeSet);
                $this->dispatch($event);

                if ($dropzone->getPeerReview()) {
                    $stayHere = $form->get('stayHere')->getData();

                    if ($stayHere == 1) {
                        if ($dropzone->hasCriteria() === false) {
                            $request->getSession()->getFlashBag()->add(
                                'warning',
                                $this->get('translator')->trans('Warning your peer review offers no criteria on which to base correct copies', array(), 'icap_dropzone')
                            );
                        }

                        $request->getSession()->getFlashBag()->add(
                            'success',
                            $this->get('translator')->trans('The evaluation has been successfully saved', array(), 'icap_dropzone')
                        );
                    } else {
                        return $this->redirect(
                            $this->generateUrl(
                                'icap_dropzone_edit_criteria',
                                array(
                                    'resourceId' => $dropzone->getId(),
                                )
                            )
                        );
                    }
                } else {
                    $request->getSession()->getFlashBag()->add(
                        'success',
                        $this->get('translator')->trans('The evaluation has been successfully saved', array(), 'icap_dropzone')
                    );
                }
            }
        }

        return array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/edit/criteria",
     *      name="icap_dropzone_edit_criteria",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     *
     * @Route(
     *      "/{resourceId}/edit/criteria/{page}",
     *      name="icap_dropzone_edit_criteria_paginated",
     *      requirements={"resourceId" = "\d+", "page" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function editCriteriaAction(Request $request, Dropzone $dropzone, $page)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('IcapDropzoneBundle:Criterion');
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
                        'icap_dropzone_edit_criteria_paginated',
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
            ->getRepository('IcapDropzoneBundle:Correction')
            ->countByDropzone($dropzone->getId());

        $form = $this->createForm(new DropzoneCriteriaType(), $dropzone);
        $add_criteria_after = false;
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $add_criteria_after = $request->request->get('addCriteria') == 'add-criterion' ? true : false;

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
                    $this->get('icap.manager.dropzone_manager')->recalculateScoreByDropzone($dropzone);
                    $request->getSession()->getFlashBag()->add(
                        'success',
                        $this->get('translator')->trans('Grades were recalculated', array(), 'icap_dropzone')
                    );
                }

                $event = new LogDropzoneConfigureEvent($dropzone, $changeSet);
                $this->dispatch($event);

                if ($dropzone->hasCriteria() === false) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        $this->get('translator')->trans('Warning your peer review offers no criteria on which to base correct copies', array(), 'icap_dropzone')
                    );
                }
                if ($add_criteria_after) {
                    return new JsonResponse(array('success' => true));
                    //$this->generateUrl('icap_dropzone_edit_add_criterion',array('resourceId'=>$dropzone->getId(),'page'=>$page));
                }

                $goBack = $form->get('goBack')->getData();
                if ($goBack == 0) {
                    $request->getSession()->getFlashBag()->add(
                        'success',
                        $this->get('translator')->trans('The evaluation has been successfully saved', array(), 'icap_dropzone')
                    );
                } else {
                    return $this->redirect(
                        $this->generateUrl(
                            'icap_dropzone_edit_common',
                            array(
                                'resourceId' => $dropzone->getId(),
                            )
                        )
                    );
                }
            }
        }

        return array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'pager' => $pager,
            'form' => $form->createView(),
            'nbCorrection' => $nbCorrection,
            'add_criteria_after' => $add_criteria_after,
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/open",
     *      name="icap_dropzone_open",
     *      requirements={"resourceId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={
     *      "authenticatedUser" = true,
     *      "messageEnabled" = true,
     *      "messageTranslationKey" = "Participate in an evaluation requires authentication. Please login.",
     *      "messageTranslationDomain" = "icap_dropzone"
     * })
     * @Template()
     */
    public function openAction(Dropzone $dropzone, $user)
    {
        //Participant view for a dropzone
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);

        $em = $this->getDoctrine()->getManager();
        $dropRepo = $em->getRepository('IcapDropzoneBundle:Drop');
        $drop = $dropRepo->findOneBy(array('dropzone' => $dropzone, 'user' => $user));
        $dropzoneManager = $this->get('icap.manager.dropzone_manager');
        // check if endAllowDrop is past and close all unvalidated
        // drops if autoclose options is activated.
        if ($dropzone->getAutoCloseState() == Dropzone::AUTO_CLOSED_STATE_WAITING) {
            $dropzoneManager->closeDropzoneOpenedDrops($dropzone);
        }

        $nbCorrections = $em
            ->getRepository('IcapDropzoneBundle:Correction')
            ->countFinished($dropzone, $user);
        $hasCopyToCorrect = $em
            ->getRepository('IcapDropzoneBundle:Drop')
            ->hasCopyToCorrect($dropzone, $user);
        $hasUnfinishedCorrection = $em->getRepository('IcapDropzoneBundle:Correction')->getNotFinished($dropzone, $user) != null;

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

    /**
     * Check if user date format is OK.
     *
     * @param $date
     * @param string $format
     *
     * @return bool
     */
    private function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        return DateTime::createFromFormat($format, $date);
    }

    private function createAgendaEventDrop($startDate, $endDate, $user, Dropzone $dropzone, $type = 'drop')
    {
        $event = new Event();
        $event->setStart($startDate->getTimeStamp());
        $event->setEnd($endDate->getTimeStamp());
        $event->setUser($user);

        $dropzoneName = $dropzone->getResourceNode()->getName();
        if ($type == 'drop') {
            $title = $this->get('translator')->trans('Deposit phase of the %dropzonename% evaluation', array('%dropzonename%' => $dropzoneName), 'icap_dropzone');
            $desc = $this->get('translator')->trans('Evaluation %dropzonename% opening', array('%dropzonename%' => $dropzoneName), 'icap_dropzone');

            $event->setTitle($title);
            $event->setDescription($desc);
        } else {
            $title = $this->get('translator')->trans('Peer Review is starting in %dropzonename% evaluation', array('%dropzonename%' => $dropzoneName), 'icap_dropzone');
            $desc = $this->get('translator')->trans('Peer Review is starting in %dropzonename% evaluation', array('%dropzonename%' => $dropzoneName), 'icap_dropzone');

            $event->setTitle($title);
            $event->setDescription($desc);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($event);
        $em->flush();

        return $event;
    }

    /**
     * @Route(
     *      "/{resourceId}/download/byDates",
     *      name="icap_dropzone_download_copies_by_date",
     *      requirements={"resourceId" = "\d+"}
     * )
     *
     *
     *
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={
     *      "authenticatedUser" = true,
     *      "messageEnabled" = true,
     *      "messageTranslationKey" = "Manage an evaluation requires authentication. Please login.",
     *      "messageTranslationDomain" = "icap_dropzone"
     * })
     * @Template()
     */
    public function downloadCopiesBetweenDatesAction(Request $request, Dropzone $dropzone)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $view = 'IcapDropzoneBundle:Drop:dropsDownloadBetweenDates.html.twig';
        if ($request->isXmlHttpRequest()) {
            $view = 'IcapDropzoneBundle:Drop:dropsDownloadBetweenDatesModal.html.twig';
        }

        $platformConfigHandler = $this->get('claroline.config.platform_config_handler');
        $form = $this->createForm(new DropsDownloadBetweenDatesType(), $dropzone, array('language' => $platformConfigHandler->getParameter('locale_language'), 'date_format' => $this->get('translator')->trans('date_form_datepicker_format', array(), 'platform')));

        return $this->render($view, array(
            'form' => $form->createView(),
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
        ));
    }

    /**
     * @Route(
     *      "/{resourceId}/download",
     *      name="icap_dropzone_download_copies",
     *      requirements={"resourceId" = "\d+"}
     * )
     *
     *
     *
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={
     *      "authenticatedUser" = true,
     *      "messageEnabled" = true,
     *      "messageTranslationKey" = "Manage an evaluation requires authentication. Please login.",
     *      "messageTranslationDomain" = "icap_dropzone"
     * })
     * @Template()
     */
    public function donwloadCopiesAction(Request $request, Dropzone $dropzone, $beginDate = null, $endDate = null)
    {
        if ($request->isMethod('POST')) {
            $date_format = $this->get('translator')->trans('date_form_datepicker_php', array(), 'platform');
            $date_format = str_replace('-', '/', $date_format);
            $date_format .= ' H:i:s'; // adding hours in order to have full day possibility ( day1 0h00 to day1 23h59 )
            $form_array = $request->request->get('icap_dropzone_date_download_between_date_form');
            if (array_key_exists('drop_period_begin_date', $form_array)) {
                $beginDate = DateTime::createFromFormat($date_format, $form_array['drop_period_begin_date'].' 00:00:00');
                // begin so day start at 00:00:00
            }
            if (array_key_exists('drop_period_end_date', $form_array)) {
                $endDate = DateTime::createFromFormat($date_format, $form_array['drop_period_end_date'].' 23:59:59');
                // end date so day end at 23:59:59
            }
        }

        $idsToDL = $this->get('icap.manager.dropzone_manager')->getResourcesNodeIdsForDownload($dropzone, $beginDate, $endDate);

        // TODO cas ou pas de document dispos à gérer
        if (count($idsToDL) <= 0) {
            $message = 'No drops to download';
            if ($beginDate != null) {
                $message = 'No drops to download in this period';
            }
            $request->getSession()->getFlashBag()->add(
                'warning',
                $this->get('translator')->trans($message, array(), 'icap_dropzone')
            );

            return $this->redirect($this->generateUrl('icap_dropzone_drops', array('resourceId' => $dropzone->getId())));
        }

        return $this->redirect(
            $this->generateUrl(
                'claro_resource_download',
                array(
                    'ids[]' => $idsToDL,
                    'forceArchive' => '1',
                )
            )
        );
    }
}
