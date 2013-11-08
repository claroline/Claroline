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
use Icap\DropzoneBundle\Entity\Correction;
use Icap\DropzoneBundle\Entity\Dropzone;
use Icap\DropzoneBundle\Entity\Grade;
use Icap\DropzoneBundle\Event\Log\LogCorrectionDeleteEvent;
use Icap\DropzoneBundle\Event\Log\LogCorrectionEndEvent;
use Icap\DropzoneBundle\Event\Log\LogCorrectionStartEvent;
use Icap\DropzoneBundle\Event\Log\LogCorrectionUpdateEvent;
use Icap\DropzoneBundle\Event\Log\LogCorrectionValidationChangeEvent;
use Icap\DropzoneBundle\Form\CorrectionCommentType;
use Icap\DropzoneBundle\Form\CorrectionCriteriaPageType;
use Icap\DropzoneBundle\Form\CorrectionStandardType;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CorrectionController extends DropzoneBaseController
{
    private function checkRightToCorrect($dropzone, $user)
    {
        $em = $this->getDoctrine()->getManager();
        // Check that the dropzone is in the process of peer review
        if ($dropzone->isPeerReview() == false) {
            $this->getRequest()->getSession()->getFlashBag()->add(
                'error',
                $this->get('translator')->trans('The peer review is not enabled', array(), 'icap_dropzone')
            );

            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_open',
                    array(
                        'resourceId' => $dropzone->getId()
                    )
                )
            );
        }

        // Check that the user has a finished dropzone for this drop.
        $userDrop = $em->getRepository('IcapDropzoneBundle:Drop')->findOneBy(array(
            'user' => $user,
            'dropzone' => $dropzone,
            'finished' => true
        ));
        if ($userDrop == null) {
            $this->getRequest()->getSession()->getFlashBag()->add(
                'error',
                $this->get('translator')->trans('You must have made ​​your copy before correcting', array(), 'icap_dropzone')
            );

            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_open',
                    array(
                        'resourceId' => $dropzone->getId()
                    )
                )
            );
        }

        // Check that the user still make corrections
        $nbCorrection = $em->getRepository('IcapDropzoneBundle:Correction')->countFinished($dropzone, $user);
        if ($nbCorrection >= $dropzone->getExpectedTotalCorrection()) {
            $this->getRequest()->getSession()->getFlashBag()->add(
                'error',
                $this->get('translator')->trans('You no longer have any copies to correct', array(), 'icap_dropzone')
            );

            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_open',
                    array(
                        'resourceId' => $dropzone->getId()
                    )
                )
            );
        }

        return null;
    }

    private function getCorrection($dropzone, $user)
    {
        $em = $this->getDoctrine()->getManager();
        // Check that the user as a not finished correction (exclude admin correction). Otherwise generate a new one.
        $correction = $em->getRepository('IcapDropzoneBundle:Correction')->getNotFinished($dropzone, $user);
        if ($correction == null) {
            $drop = $em->getRepository('IcapDropzoneBundle:Drop')->drawDropForCorrection($dropzone, $user);

            if ($drop != null) {
                $correction = new Correction();
                $correction->setDrop($drop);
                $correction->setUser($user);
                $correction->setFinished(false);
                $correction->setDropzone($dropzone);

                $em->persist($correction);
                $em->flush();

                $event = new LogCorrectionStartEvent($dropzone, $drop, $correction);
                $this->dispatch($event);
            }
        } else {
            $correction->setLastOpenDate(new \DateTime());
            $em->persist($correction);
            $em->flush();
        }

        return $correction;
    }

    private function getCriteriaPager($dropzone)
    {
        $em = $this->getDoctrine()->getManager();
        $criterionRepository = $em->getRepository('IcapDropzoneBundle:Criterion');
        $criterionQuery = $criterionRepository
            ->createQueryBuilder('criterion')
            ->andWhere('criterion.dropzone = :dropzone')
            ->setParameter('dropzone', $dropzone)
            ->orderBy('criterion.id', 'ASC');

        $adapter = new DoctrineORMAdapter($criterionQuery);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(DropzoneBaseController::CRITERION_PER_PAGE);

        return $pager;
    }

    private function persistGrade($grades, $criterionId, $value, $correction)
    {
        $em = $this->getDoctrine()->getManager();

        $grade = null;
        $i = 0;
        while ($i < count($grades) and $grade == null) {
            $current = $grades[$i];
            if (
                $current->getCriterion()->getId() == $criterionId
                and $current->getCorrection()->getId() == $correction->getId()
            ) {
                $grade = $current;
            }
            $i++;
        }

        if ($grade == null) {
            $criterionReference = $em->getReference('IcapDropzoneBundle:Criterion', $criterionId);
            $grade = new Grade();
            $grade->setCriterion($criterionReference);
            $grade->setCorrection($correction);
        }
        $grade->setValue($value);
        $em->persist($grade);
        $em->flush();

        return $grade;
    }

    private function endCorrection(Dropzone $dropzone, Correction $correction, $admin)
    {
        $em = $this->getDoctrine()->getManager();

        $edit = false;
        if ($correction->getFinished() === true) {
            $edit = true;
        }

        $correction->setEndDate(new \DateTime());
        $correction->setFinished(true);
        $totalGrade = $this->calculateCorrectionTotalGrade($dropzone, $correction);
        $correction->setTotalGrade($totalGrade);

        $em->persist($correction);
        $em->flush();

        $event = null;
        if ($edit == true) {
            $event = new LogCorrectionUpdateEvent($dropzone, $correction->getDrop(), $correction);
        } else {
            $event = new LogCorrectionEndEvent($dropzone, $correction->getDrop(), $correction);
        }
        $this->dispatch($event);

        $this->getRequest()->getSession()->getFlashBag()->add(
            'success',
            $this->get('translator')->trans('Your correction has been saved', array(), 'icap_dropzone')
        );

        if ($admin === true) {
            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_drops_detail',
                    array(
                        'resourceId' => $dropzone->getId(),
                        'dropId' => $correction->getDrop()->getId()
                    )
                )
            );
        } else {
            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_open',
                    array(
                        'resourceId' => $dropzone->getId()
                    )
                )
            );
        }

    }

    private function calculateCorrectionTotalGrade(Dropzone $dropzone, Correction $correction)
    {
        $correction->setTotalGrade(null);

        $nbCriteria = count($dropzone->getPeerReviewCriteria());
        $maxGrade = $dropzone->getTotalCriteriaColumn() - 1;
        $sumGrades = 0;
        foreach ($correction->getGrades() as $grade) {
            ($grade->getValue() > $maxGrade) ? $sumGrades += $maxGrade : $sumGrades += $grade->getValue();
        }

        $totalGrade = 0;
        if ($nbCriteria != 0) {

            $totalGrade = $sumGrades / ($nbCriteria);
            $totalGrade = ($totalGrade * 20) / ($maxGrade);
        }

        return $totalGrade;
    }

    /**
     * @Route(
     *      "/{resourceId}/correct",
     *      name="icap_dropzone_correct",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @Route(
     *      "/{resourceId}/correct/{page}",
     *      name="icap_dropzone_correct_paginated",
     *      requirements={"resourceId" = "\d+", "page" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={
     *      "authenticatedUser" = true,
     *      "messageEnabled" = true,
     *      "messageTranslationKey" = "Correct an evaluation requires authentication. Please login.",
     *      "messageTranslationDomain" = "icap_dropzone"
     * })
     * @Template()
     */
    public function correctAction($dropzone, $user, $page)
    {
        $this->isAllowToOpen($dropzone);
        $em = $this->getDoctrine()->getManager();

        $check = $this->checkRightToCorrect($dropzone, $user);
        if ($check !== null) {
            return $check;
        }

        $correction = $this->getCorrection($dropzone, $user);
        if ($correction === null) {
            $this->getRequest()->getSession()->getFlashBag()->add(
                'error',
                $this
                    ->get('translator')
                    ->trans('Unfortunately there is no copy to correct for the moment. Please try again later', array(), 'icap_dropzone')
            );

            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_open',
                    array(
                        'resourceId' => $dropzone->getId()
                    )
                )
            );
        }

        $pager = $this->getCriteriaPager($dropzone);
        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $oldData = array();
        $grades = array();
        if ($correction !== null) {
            $grades = $em
                ->getRepository('IcapDropzoneBundle:Grade')
                ->findByCriteriaAndCorrection($pager->getCurrentPageResults(), $correction);
            foreach ($grades as $grade) {
                $oldData[$grade->getCriterion()->getId()] = ($grade->getValue() >= $dropzone->getTotalCriteriaColumn())
                    ? ($dropzone->getTotalCriteriaColumn() - 1) : $grade->getValue();
            }
        }

        $form = $this->createForm(
            new CorrectionCriteriaPageType(),
            $oldData,
            array('criteria' => $pager->getCurrentPageResults(), 'totalChoice' => $dropzone->getTotalCriteriaColumn())
        );

        if ($this->getRequest()->isMethod('POST') and $correction !== null) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid()) {
                $data = $form->getData();

                foreach ($data as $criterionId => $value) {
                    $this->persistGrade($grades, $criterionId, $value, $correction);
                }

                $goBack = $form->get('goBack')->getData();
                if ($goBack == 1) {
                    $pageNumber = max(($page - 1), 0);

                    return $this->redirect(
                        $this->generateUrl(
                            'icap_dropzone_correct_paginated',
                            array(
                                'resourceId' => $dropzone->getId(),
                                'page' => $pageNumber
                            )
                        )
                    );
                } else {
                    if ($pager->getCurrentPage() < $pager->getNbPages()) {
                        return $this->redirect(
                            $this->generateUrl(
                                'icap_dropzone_correct_paginated',
                                array(
                                    'resourceId' => $dropzone->getId(),
                                    'page' => ($page + 1)
                                )
                            )
                        );
                    } else {
                        return $this->redirect(
                            $this->generateUrl(
                                'icap_dropzone_correct_comment',
                                array(
                                    'resourceId' => $dropzone->getId()
                                )
                            )
                        );
                    }
                }
            }
        }

        $view = 'IcapDropzoneBundle:Correction:correctCriteria.html.twig';

        return $this->render(
            $view,
            array(
                'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                '_resource' => $dropzone,
                'dropzone' => $dropzone,
                'correction' => $correction,
                'pager' => $pager,
                'form' => $form->createView(),
                'admin' => false,
                'edit' => true
            )
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/correct/comment",
     *      name="icap_dropzone_correct_comment",
     *      requirements={"resourceId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={
     *      "authenticatedUser" = true,
     *      "messageEnabled" = true,
     *      "messageTranslationKey" = "Correct an evaluation requires authentication. Please login.",
     *      "messageTranslationDomain" = "icap_dropzone"
     * })
     * @Template()
     */
    public function correctCommentAction(Dropzone $dropzone, User $user)
    {
        $this->isAllowToOpen($dropzone);
        $check = $this->checkRightToCorrect($dropzone, $user);
        if ($check !== null) {
            return $check;
        }

        $correction = $this->getCorrection($dropzone, $user);
        if ($correction === null) {
            $this
                ->getRequest()
                ->getSession()
                ->getFlashBag()
                ->add(
                    'error',
                    $this
                        ->get('translator')
                        ->trans('Unfortunately there is no copy to correct for the moment. Please try again later', array(), 'icap_dropzone')
                );

            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_open',
                    array(
                        'resourceId' => $dropzone->getId()
                    )
                )
            );
        }

        $pager = $this->getCriteriaPager($dropzone);
        $form = $this->createForm(new CorrectionCommentType(), $correction, array('allowCommentInCorrection' => $dropzone->getAllowCommentInCorrection()));

        if ($this->getRequest()->isMethod('POST')) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid()) {
                $correction = $form->getData();
                $em = $this->getDoctrine()->getManager();
                $em->persist($correction);
                $em->flush();

                $goBack = $form->get('goBack')->getData();
                if ($goBack == 1) {

                    return $this->redirect(
                        $this->generateUrl(
                            'icap_dropzone_correct_paginated',
                            array(
                                'resourceId' => $dropzone->getId(),
                                'page' => $pager->getNbPages()
                            )
                        )
                    );
                } else {
                    return $this->endCorrection($dropzone, $correction, false);
                }
            }
        }

        $view = 'IcapDropzoneBundle:Correction:correctComment.html.twig';
        $totalGrade = $this->calculateCorrectionTotalGrade($dropzone, $correction);

        return $this->render(
            $view,
            array(
                'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                '_resource' => $dropzone,
                'dropzone' => $dropzone,
                'correction' => $correction,
                'form' => $form->createView(),
                'nbPages' => $pager->getNbPages(),
                'admin' => false,
                'edit' => true,
                'totalGrade' => $totalGrade,
            )
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/drops/detail/correction/standard/{state}/{correctionId}",
     *      name="icap_dropzone_drops_detail_correction_standard",
     *      requirements={"resourceId" = "\d+", "correctionId" = "\d+", "state" = "show|edit"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={
     *      "authenticatedUser" = true,
     *      "messageEnabled" = true,
     *      "messageTranslationKey" = "Correct an evaluation requires authentication. Please login.",
     *      "messageTranslationDomain" = "icap_dropzone"
     * })
     */
    public function dropsDetailCorrectionStandardAction(Dropzone $dropzone, $state, $correctionId, $user)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

        /** @var Correction $correction */
        $correction = $this
            ->getDoctrine()
            ->getRepository('IcapDropzoneBundle:Correction')
            ->getCorrectionAndDropAndUserAndDocuments($dropzone, $correctionId);

        $edit = $state == 'edit';

        if ($edit === true and $correction->getEditable() === false) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new CorrectionStandardType(), $correction);

        if ($this->getRequest()->isMethod('POST')) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $event = null;
                if ($correction->getFinished() === true) {
                    $event = new LogCorrectionUpdateEvent($dropzone, $correction->getDrop(), $correction);
                } else {
                    $event = new LogCorrectionEndEvent($dropzone, $correction->getDrop(), $correction);
                }

                $correction = $form->getData();
                $correction->setEndDate(new \DateTime());
                $correction->setFinished(true);

                $em->persist($correction);
                $em->flush();

                $this->dispatch($event);

                $this->getRequest()->getSession()->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('Your correction has been saved', array(), 'icap_dropzone')
                );

                return $this->redirect(
                    $this->generateUrl(
                        'icap_dropzone_drops_detail',
                        array(
                            'resourceId' => $dropzone->getId(),
                            'dropId' => $correction->getDrop()->getId()
                        )
                    )
                );
            }
        }

        $view = 'IcapDropzoneBundle:Correction:correctStandard.html.twig';

        return $this->render(
            $view,
            array(
                'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                '_resource' => $dropzone,
                'dropzone' => $dropzone,
                'correction' => $correction,
                'form' => $form->createView(),
                'admin' => true,
                'edit' => $edit,
                'state' => $state
            )
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/drops/detail/correction/{state}/{correctionId}",
     *      name="icap_dropzone_drops_detail_correction",
     *      requirements={"resourceId" = "\d+", "correctionId" = "\d+", "state" = "show|edit"},
     *      defaults={"page" = 1}
     * )
     * @Route(
     *      "/{resourceId}/drops/detail/correction/{state}/{correctionId}/{page}",
     *      name="icap_dropzone_drops_detail_correction_paginated",
     *      requirements={"resourceId" = "\d+", "correctionId" = "\d+", "page" = "\d+", "state" = "show|edit"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={
     *      "authenticatedUser" = true,
     *      "messageEnabled" = true,
     *      "messageTranslationKey" = "Correct an evaluation requires authentication. Please login.",
     *      "messageTranslationDomain" = "icap_dropzone"
     * })
     * @Template()
     */
    public function dropsDetailCorrectionAction(Dropzone $dropzone, $state, $correctionId, $page, $user)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

        if (!$dropzone->getPeerReview()) {
            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_drops_detail_correction_standard',
                    array(
                        'resourceId' => $dropzone->getId(),
                        'state' => $state,
                        'correctionId' => $correctionId
                    )
                )
            );
        }

        /** @var Correction $correction */
        $correction = $this
            ->getDoctrine()
            ->getRepository('IcapDropzoneBundle:Correction')
            ->getCorrectionAndDropAndUserAndDocuments($dropzone, $correctionId);

        $edit = $state == 'edit';

        if ($correction == null) {
            throw new NotFoundHttpException();
        }

        if ($edit === true and $correction->getEditable() === false) {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();

        $pager = $this->getCriteriaPager($dropzone);
        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $oldData = array();
        $grades = array();
        if ($correction !== null) {
            $grades = $em
                ->getRepository('IcapDropzoneBundle:Grade')
                ->findByCriteriaAndCorrection($pager->getCurrentPageResults(), $correction);
            foreach ($grades as $grade) {
                $oldData[$grade->getCriterion()->getId()] = ($grade->getValue() >= $dropzone->getTotalCriteriaColumn())
                    ? ($dropzone->getTotalCriteriaColumn() - 1) : $grade->getValue();
            }
        }

        $form = $this->createForm(
            new CorrectionCriteriaPageType(),
            $oldData,
            array(
                'edit' => $edit,
                'criteria' => $pager->getCurrentPageResults(),
                'totalChoice' => $dropzone->getTotalCriteriaColumn()
            )
        );

        if ($edit) {
            if ($this->getRequest()->isMethod('POST') and $correction !== null) {
                $form->handleRequest($this->getRequest());
                if ($form->isValid()) {
                    $data = $form->getData();

                    foreach ($data as $criterionId => $value) {
                        $this->persistGrade($grades, $criterionId, $value, $correction);
                    }

                    if ($correction->getFinished()) {
                        $totalGrade = $this->calculateCorrectionTotalGrade($dropzone, $correction);
                        $correction->setTotalGrade($totalGrade);

                        $em->persist($correction);
                        $em->flush();
                    }
                    $goBack = $form->get('goBack')->getData();
                    if ($goBack == 1) {
                        $pageNumber = max(($page - 1), 0);

                        return $this->redirect(
                            $this->generateUrl(
                                'icap_dropzone_drops_detail_correction_paginated',
                                array(
                                    'resourceId' => $dropzone->getId(),
                                    'state' => 'edit',
                                    'correctionId' => $correction->getId(),
                                    'page' => $pageNumber,
                                )
                            )
                        );
                    } else {
                        if ($pager->getCurrentPage() < $pager->getNbPages()) {
                            return $this->redirect(
                                $this->generateUrl(
                                    'icap_dropzone_drops_detail_correction_paginated',
                                    array(
                                        'resourceId' => $dropzone->getId(),
                                        'state' => 'edit',
                                        'correctionId' => $correction->getId(),
                                        'page' => ($page + 1)
                                    )
                                )
                            );
                        } else {
                            return $this->redirect(
                                $this->generateUrl(
                                    'icap_dropzone_drops_detail_correction_comment',
                                    array(
                                        'resourceId' => $dropzone->getId(),
                                        'state' => 'edit',
                                        'correctionId' => $correction->getId()
                                    )
                                )
                            );
                        }
                    }
                }
            }
        }

        $view = 'IcapDropzoneBundle:Correction:correctCriteria.html.twig';

        return $this->render(
            $view,
            array(
                'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                '_resource' => $dropzone,
                'dropzone' => $dropzone,
                'correction' => $correction,
                'pager' => $pager,
                'form' => $form->createView(),
                'admin' => true,
                'edit' => $edit,
                'state' => $state
            )
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/drops/detail/correction/comment/{state}/{correctionId}",
     *      name="icap_dropzone_drops_detail_correction_comment",
     *      requirements={"resourceId" = "\d+", "correctionId" = "\d+", "state" = "show|edit"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={
     *      "authenticatedUser" = true,
     *      "messageEnabled" = true,
     *      "messageTranslationKey" = "Correct an evaluation requires authentication. Please login.",
     *      "messageTranslationDomain" = "icap_dropzone"
     * })
     * @Template()
     */
    public function dropsDetailCorrectionCommentAction(Dropzone $dropzone, $state, $correctionId, $user)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

        $correction = $this
            ->getDoctrine()
            ->getRepository('IcapDropzoneBundle:Correction')
            ->getCorrectionAndDropAndUserAndDocuments($dropzone, $correctionId);

        $edit = $state == 'edit';

        if ($edit === true and $correction->getEditable() === false) {
            throw new AccessDeniedException();
        }

        $pager = $this->getCriteriaPager($dropzone);
        $form = $this->createForm(new CorrectionCommentType(), $correction, array('edit' => $edit, 'allowCommentInCorrection' => $dropzone->getAllowCommentInCorrection()));

        if ($edit) {
            if ($this->getRequest()->isMethod('POST')) {
                $form->handleRequest($this->getRequest());
                if ($form->isValid()) {
                    $em = $this->getDoctrine()->getManager();
                    $correction = $form->getData();
                    $em->persist($correction);
                    $em->flush();

                    $goBack = $form->get('goBack')->getData();
                    if ($goBack == 1) {
                        return $this->redirect(
                            $this->generateUrl(
                                'icap_dropzone_drops_detail_correction_paginated',
                                array(
                                    'resourceId' => $dropzone->getId(),
                                    'state' => 'edit',
                                    'correctionId' => $correction->getId(),
                                    'page' => $pager->getNbPages(),
                                )
                            )
                        );
                    } else {
                        return $this->endCorrection($dropzone, $correction, true);
                    }
                }
            }
        }

        $view = 'IcapDropzoneBundle:Correction:correctComment.html.twig';
        $totalGrade = $this->calculateCorrectionTotalGrade($dropzone, $correction);

        return $this->render(
            $view,
            array(
                'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                '_resource' => $dropzone,
                'dropzone' => $dropzone,
                'correction' => $correction,
                'form' => $form->createView(),
                'nbPages' => $pager->getNbPages(),
                'admin' => true,
                'edit' => $edit,
                'state' => $state,
                'totalGrade' => $totalGrade,
            )
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/drops/detail/{dropId}/add/correction",
     *      name="icap_dropzone_drops_detail_add_correction",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={
     *      "authenticatedUser" = true,
     *      "messageEnabled" = true,
     *      "messageTranslationKey" = "Correct an evaluation requires authentication. Please login.",
     *      "messageTranslationDomain" = "icap_dropzone"
     * })
     * @ParamConverter("drop", class="IcapDropzoneBundle:Drop", options={"id" = "dropId"})
     * @Template()
     */
    public function dropsDetailAddCorrectionAction($dropzone, $user, $drop)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

        $em = $this->getDoctrine()->getManager();
        $correction = new Correction();
        $correction->setUser($user);
        $correction->setDropzone($dropzone);
        $correction->setDrop($drop);
        //Allow admins to edit this correction
        $correction->setEditable(true);
        $em->persist($correction);
        $em->flush();

        $event = new LogCorrectionStartEvent($dropzone, $drop, $correction);
        $this->dispatch($event);

        return $this->redirect(
            $this->generateUrl(
                'icap_dropzone_drops_detail_correction',
                array(
                    'resourceId' => $dropzone->getId(),
                    'state' => 'edit',
                    'correctionId' => $correction->getId(),
                )
            )
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/delete/correction/{correctionId}",
     *      name="icap_dropzone_drops_detail_delete_correction",
     *      requirements={"resourceId" = "\d+", "correctionId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("correction", class="IcapDropzoneBundle:Correction", options={"id" = "correctionId"})
     * @Template()
     */
    public function deleteCorrectionAction($dropzone, $correction)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

        if ($correction->getEditable() === false) {
            throw new AccessDeniedException();
        }

        $dropId = $correction->getDrop()->getId();

        $em = $this->getDoctrine()->getManager();
        $em->remove($correction);
        $em->flush();

        $event = new LogCorrectionDeleteEvent($dropzone, $correction->getDrop(), $correction);
        $this->dispatch($event);

        return $this->redirect(
            $this->generateUrl(
                'icap_dropzone_drops_detail',
                array(
                    'resourceId' => $dropzone->getId(),
                    'dropId' => $dropId,
                )
            )
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/drops/detail/correction/validation/{value}/{correctionId}",
     *      name="icap_dropzone_drops_detail_correction_validation",
     *      requirements={"resourceId" = "\d+", "correctionId" = "\d+", "value" = "no|yes"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("correction", class="IcapDropzoneBundle:Correction", options={"id" = "correctionId"})
     * @Template()
     */
    public function setCorrectionValidationAction(Dropzone $dropzone, Correction $correction, $value)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

        $em = $this->getDoctrine()->getManager();

        if ($value == 'yes') {
            $correction->setValid(true);
        } else {
            $correction->setValid(false);
        }

        $em->persist($correction);
        $em->flush();

        $event = new LogCorrectionValidationChangeEvent($dropzone, $correction->getDrop(), $correction);
        $this->dispatch($event);

        return $this->redirect(
            $this->generateUrl(
                'icap_dropzone_drops_detail',
                array(
                    'resourceId' => $dropzone->getId(),
                    'dropId' => $correction->getDrop()->getId(),
                )
            )
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/drops/detail/{dropId}/invalidate_all",
     *      name="icap_dropzone_drops_detail_invalidate_all_corrections",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("drop", class="IcapDropzoneBundle:Drop", options={"id" = "dropId"})
     * @Template()
     */
    public function invalidateAllCorrectionsAction($dropzone, $drop)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

        $this
            ->getDoctrine()
            ->getRepository('IcapDropzoneBundle:Correction')
            ->invalidateAllCorrectionForADrop($dropzone, $drop);

        //TODO invalidate all correction event

        return $this->redirect(
            $this->generateUrl(
                'icap_dropzone_drops_detail',
                array(
                    'resourceId' => $dropzone->getId(),
                    'dropId' => $drop->getId(),
                )
            )
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/recalculate/score/{correctionId}",
     *      name="icap_dropzone_recalculate_score",
     *      requirements={"resourceId" = "\d+", "correctionId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("correction", class="IcapDropzoneBundle:Correction", options={"id" = "correctionId"})
     * @Template()
     */
    public function recalculateScoreAction(Dropzone $dropzone, Correction $correction)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

        if (!$dropzone->getPeerReview()) {
            throw new AccessDeniedException();
        }

        $oldTotalGrade = $correction->getTotalGrade();

        $totalGrade = $this->calculateCorrectionTotalGrade($dropzone, $correction);
        $correction->setTotalGrade($totalGrade);
        $em = $this->getDoctrine()->getManager();

        $em->persist($correction);
        $em->flush();

        if ($oldTotalGrade != $totalGrade) {
            $event = new LogCorrectionUpdateEvent($dropzone, $correction->getDrop(), $correction);
            $this->dispatch($event);
        }

        return $this->redirect(
            $this->generateUrl(
                'icap_dropzone_drops_detail',
                array(
                    'resourceId' => $dropzone->getId(),
                    'dropId' => $correction->getDrop()->getId(),
                )
            )
        );
    }
}