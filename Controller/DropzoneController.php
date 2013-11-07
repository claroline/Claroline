<?php
/**
 * Created by : Vincent SAISSET
 * Date: 22/08/13
 * Time: 09:30
 */

namespace Icap\DropzoneBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Event\Log\LogResourceUpdateEvent;
use Icap\DropzoneBundle\Entity\Dropzone;
use Icap\DropzoneBundle\Event\Log\LogDropzoneConfigureEvent;
use Icap\DropzoneBundle\Form\DropzoneCommonType;
use Icap\DropzoneBundle\Form\DropzoneCriteriaType;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormError;
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
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function editCommonAction(Dropzone $dropzone)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

        $form = $this->createForm(new DropzoneCommonType(), $dropzone);

        if ($this->getRequest()->isMethod('POST')) {
            $form->handleRequest($this->getRequest());

            /** @var Dropzone $dropzone */
            $dropzone = $form->getData();

            if (!$dropzone->getPeerReview() and $dropzone->getManualState() == 'peerReview') {
                $dropzone->setManualState('notStarted');
            }
            if ($dropzone->getEditionState() < 2) {
                $dropzone->setEditionState(2);
            }

            if (!$dropzone->getDisplayNotationToLearners() and !$dropzone->getDisplayNotationMessageToLearners()) {
                $form->get('displayNotationToLearners')->addError(new FormError('Choose at least one type of ranking'));
                $form
                    ->get('displayNotationMessageToLearners')
                    ->addError(new FormError('Choose at least one type of ranking'));
            }

            if (
                !$dropzone->getAllowWorkspaceResource()
                and !$dropzone->getAllowUpload()
                and !$dropzone->getAllowUrl()
                and !$dropzone->getAllowRichText()
            ) {
                $form->get('allowWorkspaceResource')->addError(new FormError('Choose at least one type of document'));
                $form->get('allowUpload')->addError(new FormError('Choose at least one type of document'));
                $form->get('allowUrl')->addError(new FormError('Choose at least one type of document'));
                $form->get('allowRichText')->addError(new FormError('Choose at least one type of document'));
            }

            if (!$dropzone->getManualPlanning()) {
                if ($dropzone->getStartAllowDrop() === null) {
                    $form->get('startAllowDrop')->addError(new FormError('Choose a date'));
                }
                if ($dropzone->getEndAllowDrop() === null) {
                    $form->get('endAllowDrop')->addError(new FormError('Choose a date'));
                }
                if ($dropzone->getPeerReview() && $dropzone->getEndReview() === null) {
                    $form->get('endReview')->addError(new FormError('Choose a date'));
                }
                if ($dropzone->getStartAllowDrop() !== null && $dropzone->getEndAllowDrop() !== null) {
                    if ($dropzone->getStartAllowDrop()->getTimestamp() > $dropzone->getEndAllowDrop()->getTimestamp()) {
                        $form->get('startAllowDrop')->addError(new FormError('Must be before end allow drop'));
                        $form->get('endAllowDrop')->addError(new FormError('Must be after start allow drop'));
                    }
                }
                if ($dropzone->getStartReview() !== null && $dropzone->getEndReview() !== null) {
                    if ($dropzone->getStartReview()->getTimestamp() > $dropzone->getEndReview()->getTimestamp()) {
                        $form->get('startReview')->addError(new FormError('Must be before end peer review'));
                        $form->get('endReview')->addError(new FormError('Must be after start peer review'));
                    }
                }
                if ($dropzone->getStartAllowDrop() !== null && $dropzone->getStartReview() !== null) {
                    if ($dropzone->getStartAllowDrop()->getTimestamp() > $dropzone->getStartReview()->getTimestamp()) {
                        $form->get('startReview')->addError(new FormError('Must be after start allow drop'));
                        $form->get('startAllowDrop')->addError(new FormError('Must be before start peer review'));
                    }
                }
                if ($dropzone->getEndAllowDrop() !== null && $dropzone->getEndReview() !== null) {
                    if ($dropzone->getEndAllowDrop()->getTimestamp() > $dropzone->getEndReview()->getTimestamp()) {
                        $form->get('endReview')->addError(new FormError('Must be after end allow drop'));
                        $form->get('endAllowDrop')->addError(new FormError('Must be before end peer review'));
                    }
                }
            }

            if ($form->isValid()) {
                if ($dropzone->getPeerReview() != true) {
                    $dropzone->setExpectedTotalCorrection(1);
                    if ($dropzone->getManualState() == 'peerReview') {
                        $dropzone->setManualState('notStarted');
                    }
                }

                $em = $this->getDoctrine()->getManager();

                $unitOfWork = $em->getUnitOfWork();
                $unitOfWork->computeChangeSets();
                $changeSet = $unitOfWork->getEntityChangeSet($dropzone);

                $em->persist($dropzone);
                $em->flush();

                $event = new LogDropzoneConfigureEvent($dropzone, $changeSet);
                $this->dispatch($event);

                if ($dropzone->getPeerReview()) {

                    $stayHere = $form->get('stayHere')->getData();

                    if ($stayHere == 1) {
                        if ($dropzone->hasCriteria() === false) {
                            $this->getRequest()->getSession()->getFlashBag()->add(
                                'warning',
                                $this->get('translator')->trans('Warning your peer review offers no criteria on which to base correct copies', array(), 'icap_dropzone')
                            );
                        }

                        $this->getRequest()->getSession()->getFlashBag()->add(
                            'success',
                            $this->get('translator')->trans('The evaluation has been successfully saved', array(), 'icap_dropzone')
                        );
                    } else {
                        return $this->redirect(
                            $this->generateUrl(
                                'icap_dropzone_edit_criteria',
                                array(
                                    'resourceId' => $dropzone->getId()
                                )
                            )
                        );
                    }
                } else {
                    $this->getRequest()->getSession()->getFlashBag()->add(
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
            'form' => $form->createView()
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
    public function editCriteriaAction(Dropzone $dropzone, $page)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

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
                            'page' => $pager->getNbPages()
                        )
                    )
                );
            } else {
                throw new NotFoundHttpException();
            }
        }

        $form = $this->createForm(new DropzoneCriteriaType(), $dropzone);

        if ($this->getRequest()->isMethod('POST')) {
            $form->handleRequest($this->getRequest());

            if ($form->isValid()) {
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

                $event = new LogDropzoneConfigureEvent($dropzone, $changeSet);
                $this->dispatch($event);

                if ($dropzone->hasCriteria() === false) {
                    $this->getRequest()->getSession()->getFlashBag()->add(
                        'warning',
                        $this->get('translator')->trans('Warning your peer review offers no criteria on which to base correct copies', array(), 'icap_dropzone')
                    );
                }

                $goBack = $form->get('goBack')->getData();
                if ($goBack == 0) {
                    $this->getRequest()->getSession()->getFlashBag()->add(
                        'success',
                        $this->get('translator')->trans('The evaluation has been successfully saved', array(), 'icap_dropzone')
                    );
                } else {
                    return $this->redirect(
                        $this->generateUrl(
                            'icap_dropzone_edit_common',
                            array(
                                'resourceId' => $dropzone->getId()
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
            'form' => $form->createView()
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
        $this->isAllowToOpen($dropzone);

        $em = $this->getDoctrine()->getManager();
        $dropRepo = $em->getRepository('IcapDropzoneBundle:Drop');
        $drop = $dropRepo->findOneBy(array('dropzone' => $dropzone, 'user' => $user));

        $nbCorrections = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('IcapDropzoneBundle:Correction')
            ->countFinished($dropzone, $user);
        $hasCopyToCorrect = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('IcapDropzoneBundle:Drop')
            ->hasCopyToCorrect($dropzone, $user);
        $hasUnfinishedCorrection = $em->getRepository('IcapDropzoneBundle:Correction')->getNotFinished($dropzone, $user) != null;

        return array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'drop' => $drop,
            'nbCorrections' => $nbCorrections,
            'hasCopyToCorrect' => $hasCopyToCorrect,
            'hasUnfinishedCorrection' => $hasUnfinishedCorrection,
        );
    }
}