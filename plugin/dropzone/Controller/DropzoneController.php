<?php

namespace Icap\DropzoneBundle\Controller;

use Claroline\AgendaBundle\Entity\Event;
use DateTime;
use Icap\DropzoneBundle\Entity\Dropzone;
use Icap\DropzoneBundle\Event\Log\LogDropzoneConfigureEvent;
use Icap\DropzoneBundle\Event\Log\LogDropzoneManualStateChangedEvent;
use Icap\DropzoneBundle\Form\DropsDownloadBetweenDatesType;
use Icap\DropzoneBundle\Form\DropzoneCommonType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

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
        $dateFormat = $this->get('translator')->trans('date_form_format', [], 'platform');
        $lang = $platformConfigHandler->getParameter('locale_language');
        $form = $this->createForm(
            DropzoneCommonType::class,
            $dropzone,
            ['language' => $lang, 'date_format' => $dateFormat]
        );

        if ($request->isMethod('POST')) {
            // see if manual plannification option has changed.
            $oldManualPlanning = $dropzone->getManualPlanning();
            $oldManualPlanningOption = $dropzone->getManualState();

            $oldEndDropDate = $dropzone->getEndAllowDrop();

            $form->handleRequest($request);

            /** @var Dropzone $dropzone */
            $dropzone = $form->getData();

            if (!$dropzone->getPeerReview() && 'peerReview' === $dropzone->getManualState()) {
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

                    $AgendaManager = $this->get('Claroline\AgendaBundle\Manager\AgendaManager');
                    $workspace = $dropzone->getResourceNode()->getWorkspace();
                    //Set the Agenda Drop Events.
                    if (null !== $dropzone->getStartAllowDrop() && null !== $dropzone->getEndAllowDrop()) {
                        //if event already exist
                        if (null !== $dropzone->getEventDrop()) {
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
                    if (null !== $dropzone->getStartReview() && null !== $dropzone->getEndReview()) {
                        // if event is already linked.
                        if (null !== $dropzone->getEventCorrection()) {
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

                    if (null === $dropzone->getStartAllowDrop()) {
                        $form->get('startAllowDrop')->addError(new FormError('Choose a date'));
                    }
                    if (null === $dropzone->getEndAllowDrop()) {
                        $form->get('endAllowDrop')->addError(new FormError('Choose a date'));
                    }
                    if ($dropzone->getPeerReview() && null === $dropzone->getEndReview()) {
                        $form->get('endReview')->addError(new FormError('Choose a date'));
                    }
                    if (null !== $dropzone->getStartAllowDrop() && null !== $dropzone->getEndAllowDrop()) {
                        if ($dropzone->getStartAllowDrop()->getTimestamp() > $dropzone->getEndAllowDrop()->getTimestamp()) {
                            $form->get('startAllowDrop')->addError(new FormError('Must be before end allow drop'));
                            $form->get('endAllowDrop')->addError(new FormError('Must be after start allow drop'));
                        }
                    }
                    if (null !== $dropzone->getStartReview() && null !== $dropzone->getEndReview()) {
                        if ($dropzone->getStartReview()->getTimestamp() > $dropzone->getEndReview()->getTimestamp()) {
                            $form->get('startReview')->addError(new FormError('Must be before end peer review'));
                            $form->get('endReview')->addError(new FormError('Must be after start peer review'));
                        }
                    }
                    if (null !== $dropzone->getStartAllowDrop() && null !== $dropzone->getStartReview()) {
                        if ($dropzone->getStartAllowDrop()->getTimestamp() > $dropzone->getStartReview()->getTimestamp()) {
                            $form->get('startReview')->addError(new FormError('Must be after start allow drop'));
                            $form->get('startAllowDrop')->addError(new FormError('Must be before start peer review'));
                        }
                    }
                    if (null !== $dropzone->getEndAllowDrop() && null !== $dropzone->getEndReview()) {
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
                $AgendaManager = $this->get('Claroline\AgendaBundle\Manager\AgendaManager');

                if (null !== $dropzone->getEventDrop()) {
                    $event = $dropzone->getEventDrop();
                    $AgendaManager->deleteEvent($event);
                    $dropzone->setEventDrop(null);
                }

                if (null !== $dropzone->getEventCorrection()) {
                    $event = $dropzone->getEventCorrection();
                    $AgendaManager->deleteEvent($event);
                    $dropzone->setEventCorrection(null);
                }
            }

            if ($form->isValid()) {
                //getting the dropzoneManager
                $dropzoneManager = $this->get('icap.manager.dropzone_manager');

                if (true !== $dropzone->getPeerReview()) {
                    $dropzone->setExpectedTotalCorrection(1);
                    if ('peerReview' === $dropzone->getManualState()) {
                        $dropzone->setManualState('notStarted');
                    }
                }

                $manualStateChanged = false;
                $newManualState = null;
                if (true === $dropzone->getManualPlanning()) {
                    if (false === $oldManualPlanning || $oldManualPlanningOption !== $dropzone->getManualState()) {
                        $manualStateChanged = true;
                        $newManualState = $dropzone->getManualState();
                    }
                    // option auto Close unterminated drops
                    if (1 === $form->get('autoCloseForManualStates')->getData()) {
                        $dropzoneManager->closeDropzoneOpenedDrops($dropzone, true);
                    }
                } else {
                    if ($oldEndDropDate !== $dropzone->getEndAllowDrop()) {
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

                    if (1 === $stayHere) {
                        if (false === $dropzone->hasCriteria()) {
                            $request->getSession()->getFlashBag()->add(
                                'warning',
                                $this->get('translator')->trans('Warning your peer review offers no criteria on which to base correct copies', [], 'icap_dropzone')
                            );
                        }

                        $request->getSession()->getFlashBag()->add(
                            'success',
                            $this->get('translator')->trans('The evaluation has been successfully saved', [], 'icap_dropzone')
                        );
                    } else {
                        return $this->redirect(
                            $this->generateUrl(
                                'icap_dropzone_edit_criteria',
                                [
                                    'resourceId' => $dropzone->getId(),
                                ]
                            )
                        );
                    }
                } else {
                    $request->getSession()->getFlashBag()->add(
                        'success',
                        $this->get('translator')->trans('The evaluation has been successfully saved', [], 'icap_dropzone')
                    );
                }
            }
        }

        return [
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'form' => $form->createView(),
        ];
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
        $drop = $dropRepo->findOneBy(['dropzone' => $dropzone, 'user' => $user]);
        $dropzoneManager = $this->get('icap.manager.dropzone_manager');
        // check if endAllowDrop is past and close all unvalidated
        // drops if autoclose options is activated.
        if (Dropzone::AUTO_CLOSED_STATE_WAITING === $dropzone->getAutoCloseState()) {
            $dropzoneManager->closeDropzoneOpenedDrops($dropzone);
        }

        $nbCorrections = $em
            ->getRepository('IcapDropzoneBundle:Correction')
            ->countFinished($dropzone, $user);
        $hasCopyToCorrect = $em
            ->getRepository('IcapDropzoneBundle:Drop')
            ->hasCopyToCorrect($dropzone, $user);
        $hasUnfinishedCorrection = null !== $em->getRepository('IcapDropzoneBundle:Correction')->getNotFinished($dropzone, $user);

        // get progression of the evaluation ( current state, all states available and needed infos to the view).
        $dropzoneProgress = $dropzoneManager->getDrozponeProgress($dropzone, $drop, $nbCorrections);

        $PeerReviewEndCase = $dropzoneManager->isPeerReviewEndedOrManualStateFinished($dropzone, $nbCorrections);

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
        if ('drop' === $type) {
            $title = $this->get('translator')->trans('Deposit phase of the %dropzonename% evaluation', ['%dropzonename%' => $dropzoneName], 'icap_dropzone');
            $desc = $this->get('translator')->trans('Evaluation %dropzonename% opening', ['%dropzonename%' => $dropzoneName], 'icap_dropzone');

            $event->setTitle($title);
            $event->setDescription($desc);
        } else {
            $title = $this->get('translator')->trans('Peer Review is starting in %dropzonename% evaluation', ['%dropzonename%' => $dropzoneName], 'icap_dropzone');
            $desc = $this->get('translator')->trans('Peer Review is starting in %dropzonename% evaluation', ['%dropzonename%' => $dropzoneName], 'icap_dropzone');

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

        $view = 'IcapDropzoneBundle:drop:drops_download_between_dates.html.twig';
        if ($request->isXmlHttpRequest()) {
            $view = 'IcapDropzoneBundle:drop:drops_download_between_dates_modal.html.twig';
        }

        $platformConfigHandler = $this->get('claroline.config.platform_config_handler');
        $form = $this->createForm(DropsDownloadBetweenDatesType, $dropzone, ['language' => $platformConfigHandler->getParameter('locale_language'), 'date_format' => $this->get('translator')->trans('date_form_datepicker_format', [], 'platform')]);

        return $this->render($view, [
            'form' => $form->createView(),
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
        ]);
    }

    /**
     * @Route(
     *      "/{resourceId}/download",
     *      name="icap_dropzone_download_copies",
     *      requirements={"resourceId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={
     *      "authenticatedUser" = true,
     *      "messageEnabled" = true,
     *      "messageTranslationKey" = "Manage an evaluation requires authentication. Please login.",
     *      "messageTranslationDomain" = "icap_dropzone"
     * })
     */
    public function donwloadCopiesAction(Request $request, Dropzone $dropzone, $beginDate = null, $endDate = null)
    {
        if ($request->isMethod('POST')) {
            $date_format = $this->get('translator')->trans('date_form_datepicker_php', [], 'platform');
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
            if (null !== $beginDate) {
                $message = 'No drops to download in this period';
            }
            $request->getSession()->getFlashBag()->add(
                'warning',
                $this->get('translator')->trans($message, [], 'icap_dropzone')
            );

            return $this->redirect($this->generateUrl('icap_dropzone_drops', ['resourceId' => $dropzone->getId()]));
        }

        return $this->redirect(
            $this->generateUrl(
                'claro_resource_download',
                [
                    'ids[]' => $idsToDL,
                    'forceArchive' => '1',
                ]
            )
        );
    }
}
