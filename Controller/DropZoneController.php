<?php
/**
 * Created by : Vincent SAISSET
 * Date: 22/08/13
 * Time: 09:30
 */

namespace ICAP\DropZoneBundle\Controller;

use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use ICAP\DropZoneBundle\Entity\Correction;
use ICAP\DropZoneBundle\Entity\Criterion;
use ICAP\DropZoneBundle\Entity\Document;
use ICAP\DropZoneBundle\Entity\Drop;
use ICAP\DropZoneBundle\Entity\DropZone;
use ICAP\DropZoneBundle\Entity\Grade;
use ICAP\DropZoneBundle\Form\CorrectCriteriaPageType;
use ICAP\DropZoneBundle\Form\CorrectionCommentType;
use ICAP\DropZoneBundle\Form\CorrectionReportType;
use ICAP\DropZoneBundle\Form\CriterionDeleteType;
use ICAP\DropZoneBundle\Form\CriterionType;
use ICAP\DropZoneBundle\Form\DocumentDeleteType;
use ICAP\DropZoneBundle\Form\DocumentType;
use ICAP\DropZoneBundle\Form\DropType;
use ICAP\DropZoneBundle\Form\DropZoneCommonType;
use ICAP\DropZoneBundle\Form\DropZoneCriteriaType;
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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class DropZoneController extends Controller {

    const CRITERION_PER_PAGE = 10;
    const DROP_PER_PAGE = 10;

    protected function isAllow($dropZone, $actionName)
    {
        $collection = new ResourceCollection(array($dropZone->getResourceNode()));
        if (false === $this->get('security.context')->isGranted($actionName, $collection)) {
            throw new AccessDeniedException();
        }
    }

    protected function isAllowToEdit($dropZone)
    {
        $this->isAllow($dropZone, 'EDIT');
    }

    protected function isAllowToOpen($dropZone)
    {
        $this->isAllow($dropZone, 'OPEN');
    }

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
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @Template()
     */
    public function editCommonAction($dropZone)
    {
        $this->isAllowToOpen($dropZone);
        $this->isAllowToEdit($dropZone);

//        $dropZone->setName($dropZone->getResourceNode()->getName());

        $form = $this->createForm(new DropZoneCommonType(), $dropZone);//, array('language' => $this->container->getParameter('locale')));

        if ($this->getRequest()->isMethod('POST')) {
            $form->handleRequest($this->getRequest());

            $dropZone = $form->getData();

            if (!$dropZone->getPeerReview() and $dropZone->getManualState() == 'peerReview') {
                $dropZone->setManualState('notStarted');
            }
            if ($dropZone->getEditionState() < 2) {
                $dropZone->setEditionState(2);
            }

            if (!$dropZone->getDisplayNotationToLearners() and ! $dropZone->getDisplayNotationMessageToLearners()) {
                $form->get('displayNotationToLearners')->addError(new FormError('Choose at least one type of ranking'));
                $form->get('displayNotationMessageToLearners')->addError(new FormError('Choose at least one type of ranking'));
            }

            if (!$dropZone->getAllowWorkspaceResource() and !$dropZone->getAllowUpload() and !$dropZone->getAllowUrl()) {
                $form->get('allowWorkspaceResource')->addError(new FormError('Choose at least one type of document'));
                $form->get('allowUpload')->addError(new FormError('Choose at least one type of document'));
                $form->get('allowUrl')->addError(new FormError('Choose at least one type of document'));
            }

            if (!$dropZone->getManualPlanning()) {
                if ($dropZone->getStartAllowDrop() === null) {
                    $form->get('startAllowDrop')->addError(new FormError('Choose a date'));
                }
                if ($dropZone->getEndAllowDrop() === null) {
                    $form->get('endAllowDrop')->addError(new FormError('Choose a date'));
                }
                if ($dropZone->getPeerReview() && $dropZone->getEndReview() === null) {
                    $form->get('endReview')->addError(new FormError('Choose a date'));
                }
                if ($dropZone->getStartAllowDrop() !== null && $dropZone->getEndAllowDrop() !== null) {
                    if ($dropZone->getStartAllowDrop()->getTimestamp() > $dropZone->getEndAllowDrop()->getTimestamp()) {
                        $form->get('startAllowDrop')->addError(new FormError('Must be before end allow drop'));
                        $form->get('endAllowDrop')->addError(new FormError('Must be after start allow drop'));
                    }
                }
                if ($dropZone->getStartReview() !== null && $dropZone->getEndReview() !== null) {
                    if ($dropZone->getStartReview()->getTimestamp() > $dropZone->getEndReview()->getTimestamp()) {
                        $form->get('startReview')->addError(new FormError('Must be before end peer review'));
                        $form->get('endReview')->addError(new FormError('Must be after start peer review'));
                    }
                }
                if($dropZone->getStartAllowDrop() !== null && $dropZone->getStartReview() !== null) {
                    if ($dropZone->getStartAllowDrop()->getTimestamp() > $dropZone->getStartReview()->getTimestamp()) {
                        $form->get('startReview')->addError(new FormError('Must be after start allow drop'));
                        $form->get('startAllowDrop')->addError(new FormError('Must be before start peer review'));
                    }
                }
                if($dropZone->getEndAllowDrop() !== null && $dropZone->getEndReview() !== null) {
                    if ($dropZone->getEndAllowDrop()->getTimestamp() > $dropZone->getEndReview()->getTimestamp()) {
                        $form->get('endReview')->addError(new FormError('Must be after end allow drop'));
                        $form->get('endAllowDrop')->addError(new FormError('Must be before end peer review'));
                    }
                }
            }

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($dropZone);
                $em->flush();

                if ($dropZone->getPeerReview()) {
                    return $this->redirect(
                        $this->generateUrl(
                            'icap_dropzone_edit_criteria',
                            array(
                                'resourceId' => $dropZone->getId()
                            )
                        )
                    );
                } else {
                    $this->getRequest()->getSession()->getFlashBag()->add('success', $this->get('translator')->trans('The evaluation has been successfully saved'));
                }
            }
        }

        return array(
            'workspace' => $dropZone->getResourceNode()->getWorkspace(),
            'dropZone' => $dropZone,
            'pathArray' => $dropZone->getPathArray(),
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
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @Template()
     */
    public function editCriteriaAction($dropZone, $page)
    {
        $this->isAllowToOpen($dropZone);
        $this->isAllowToEdit($dropZone);

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('ICAPDropZoneBundle:Criterion');
        $query = $repository
            ->createQueryBuilder('criterion')
            ->andWhere('criterion.dropZone = :dropZone')
            ->setParameter('dropZone', $dropZone)
            ->orderBy('criterion.id', 'ASC');

        $adapter = new DoctrineORMAdapter($query);
        $pager   = new Pagerfanta($adapter);
        $pager->setMaxPerPage(self::CRITERION_PER_PAGE);
        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            if ($page > 0) {
                return $this->redirect(
                    $this->generateUrl(
                        'icap_dropzone_edit_criteria_paginated',
                        array(
                            'resourceId' => $dropZone->getId(),
                            'page' => $pager->getNbPages()
                        )
                    )
                );
            } else {
                throw new NotFoundHttpException();
            }
        }

        $form = $this->createForm(new DropZoneCriteriaType(), $dropZone);

        if ($this->getRequest()->isMethod('POST')) {
            $form->handleRequest($this->getRequest());

            if ($form->isValid()) {
                $dropZone = $form->getData();
                if ($dropZone->getEditionState() < 3) {
                    $dropZone->setEditionState(3);
                }

                $em = $this->getDoctrine()->getManager();
                $em->persist($dropZone);
                $em->flush();

                $this->getRequest()->getSession()->getFlashBag()->add('success', $this->get('translator')->trans('The evaluation has been successfully saved'));
            }
        }

        return array(
            'workspace' => $dropZone->getResourceNode()->getWorkspace(),
            'dropZone' => $dropZone,
            'pathArray' => $dropZone->getPathArray(),
            'pager' => $pager,
            'form' => $form->createView()
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/edit/addcriterion/{page}/{criterionId}",
     *      name="icap_dropzone_edit_add_criterion",
     *      requirements={"resourceId" = "\d+", "criterionId" = "\d+", "page" = "\d+"},
     *      defaults={"criterionId" = 0}
     * )
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @Template()
     */
    public function editAddCriterionAction($dropZone, $page, $criterionId)
    {
        $this->isAllowToOpen($dropZone);
        $this->isAllowToEdit($dropZone);

        $criterion = new Criterion();
        if ($criterionId != 0) {
            $criterion = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('ICAPDropZoneBundle:Criterion')
                ->find($criterionId);
        } else {
            $criterion->setDropZone($dropZone);
        }

        $form = $this->createForm(new CriterionType(), $criterion);

        if ($this->getRequest()->isXMLHttpRequest()) {

            return $this->render(
                'ICAPDropZoneBundle:DropZone:editAddCriterionModal.html.twig',
                array(
                    'workspace' => $dropZone->getResourceNode()->getWorkspace(),
                    'dropZone' => $dropZone,
                    'pathArray' => $dropZone->getPathArray(),
                    'form' => $form->createView(),
                    'criterion' => $criterion,
                    'page' => $page
                )
            );
        }

        return array(
            'workspace' => $dropZone->getResourceNode()->getWorkspace(),
            'dropZone' => $dropZone,
            'pathArray' => $dropZone->getPathArray(),
            'form' => $form->createView(),
            'criterion' => $criterion,
            'page' => $page
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/edit/createcriterion/{page}/{criterionId}",
     *      name="icap_dropzone_edit_create_criterion",
     *      requirements={"resourceId" = "\d+", "criterionId" = "\d+", "page" = "\d+"},
     *      defaults={"criterionId" = 0}
     * )
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @Template("ICAPDropZoneBundle:DropZone:editAddCriteria.html.twig")
     */
    public function editCreateCriterionAction($dropZone, $page, $criterionId)
    {
        $this->isAllowToOpen($dropZone);
        $this->isAllowToEdit($dropZone);


        $criterion = new Criterion();
        if ($criterionId != 0) {
            $criterion = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('ICAPDropZoneBundle:Criterion')
                ->find($criterionId);
        } else {
            $criterion->setDropZone($dropZone);
        }

        $form = $this->createForm(new CriterionType(), $criterion);
        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $criterion = $form->getData();
            $criterion->setDropZone($dropZone);

            $em = $this->getDoctrine()->getManager();
            $em->persist($criterion);
            $em->persist($dropZone);
            $em->flush();

            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_edit_criteria_paginated',
                    array(
                        'resourceId' => $dropZone->getId(),
                        'page' => $page
                    )
                )
            );
        }

        return array(
            'workspace' => $dropZone->getResourceNode()->getWorkspace(),
            'dropZone' => $dropZone,
            'pathArray' => $dropZone->getPathArray(),
            'form' => $form->createView(),
            'criterion' => $criterion,
            'page' => $page
        );
    }


    /**
     * @Route(
     *      "/{resourceId}/edit/deletecriterion/{page}/{criterionId}/{number}",
     *      name="icap_dropzone_edit_delete_criterion",
     *      requirements={"resourceId" = "\d+", "criterionId" = "\d+", "page" = "\d+", "number" = "\d+"}
     * )
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @ParamConverter("criterion", class="ICAPDropZoneBundle:Criterion", options={"id" = "criterionId"})
     * @Template()
     */
    public function editDeleteCriterionAction($dropZone, $page, $criterion, $number)
    {
        $this->isAllowToOpen($dropZone);
        $this->isAllowToEdit($dropZone);

        $form = $this->createForm(new CriterionDeleteType(), $criterion);


        if ($this->getRequest()->isXMLHttpRequest()) {

            return $this->render(
                'ICAPDropZoneBundle:DropZone:editDeleteCriterionModal.html.twig',
                array(
                    'workspace' => $dropZone->getResourceNode()->getWorkspace(),
                    'dropZone' => $dropZone,
                    'pathArray' => $dropZone->getPathArray(),
                    'criterion' => $criterion,
                    'form' => $form->createView(),
                    'page' => $page,
                    'number' => $number
                )
            );
        }

        return array(
            'workspace' => $dropZone->getResourceNode()->getWorkspace(),
            'dropZone' => $dropZone,
            'pathArray' => $dropZone->getPathArray(),
            'criterion' => $criterion,
            'form' => $form->createView(),
            'page' => $page,
            'number' => $number
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/edit/removecriterion/{page}/{criterionId}",
     *      name="icap_dropzone_edit_remove_criterion",
     *      requirements={"resourceId" = "\d+", "criterionId" = "\d+", "page" = "\d+"}
     * )
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @ParamConverter("criterion", class="ICAPDropZoneBundle:Criterion", options={"id" = "criterionId"})
     * @Template("ICAPDropZoneBundle:DropZone:editDeleteCriterion.html.twig")
     */
    public function editRemoveCriterionAction($dropZone, $page, $criterion)
    {
        $this->isAllowToOpen($dropZone);
        $this->isAllowToEdit($dropZone);

        $form = $this->createForm(new CriterionDeleteType(), $criterion);
        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $criterion = $form->getData();
            $criterion->setDropZone($dropZone);

            $em = $this->getDoctrine()->getManager();
            $em->remove($criterion);
            $em->flush();

            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_edit_criteria_paginated',
                    array(
                        'resourceId' => $dropZone->getId(),
                        'page' => $page
                    )
                )
            );
        }

        return array(
            'workspace' => $dropZone->getResourceNode()->getWorkspace(),
            'dropZone' => $dropZone,
            'pathArray' => $dropZone->getPathArray(),
            'criterion' => $criterion,
            'form' => $form->createView(),
            'page' => $page
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/open",
     *      name="icap_dropzone_open",
     *      requirements={"resourceId" = "\d+"}
     * )
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function openAction($dropZone, $user)
    {
        //Participant view for a dropZone
        $this->isAllowToOpen($dropZone);

        $em = $this->getDoctrine()->getManager();
        $dropRepo = $em->getRepository('ICAPDropZoneBundle:Drop');
        $drop = $dropRepo->findOneBy(array('dropZone' => $dropZone, 'user' => $user, 'finished' => true));

        $nbCorrections = $this->getDoctrine()->getManager()->getRepository('ICAPDropZoneBundle:Correction')->countFinished($dropZone, $user);

        return array(
            'workspace' => $dropZone->getResourceNode()->getWorkspace(),
            'pathArray' => $dropZone->getPathArray(),
            'dropZone' => $dropZone,
            'drop' => $drop,
            'nbCorrections' => $nbCorrections,
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/drop",
     *      name="icap_dropzone_drop",
     *      requirements={"resourceId" = "\d+"}
     * )
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function dropAction($dropZone, $user)
    {
        $this->isAllowToOpen($dropZone);

        $em = $this->getDoctrine()->getManager();
        $dropRepo = $em->getRepository('ICAPDropZoneBundle:Drop');

        if ($dropRepo->findOneBy(array('dropZone' => $dropZone, 'user' => $user, 'finished' => true)) !== null) {
            $this->getRequest()->getSession()->getFlashBag()->add('danger', $this->get('translator')->trans('You ve already made ​​your copy for this review'));

            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_open',
                    array(
                        'resourceId' => $dropZone->getId()
                    )
                )
            );
        }

        $notFinishedDrop = $dropRepo->findOneBy(array('dropZone' => $dropZone, 'user' => $user, 'finished' => false));
        if ($notFinishedDrop === null) {
            $notFinishedDrop = new Drop();
            $notFinishedDrop->setUser($user);
            $notFinishedDrop->setDropZone($dropZone);
            $notFinishedDrop->setFinished(false);

            $em->persist($notFinishedDrop);
            $em->flush();
            $em->refresh($notFinishedDrop);
        }

        $form = $this->createForm(new DropType(), $notFinishedDrop);
        $drop = $notFinishedDrop;

        if ($this->getRequest()->isMethod('POST')) {
            $form->handleRequest($this->getRequest());

            if (count($notFinishedDrop->getDocuments()) == 0) {
                $form->addError(new FormError('Add at least one document'));
            }

            if ($form->isValid()) {
                $notFinishedDrop->setFinished(true);

                $em = $this->getDoctrine()->getManager();
                $em->persist($notFinishedDrop);
                $em->flush();

                return $this->redirect(
                    $this->generateUrl(
                        'icap_dropzone_open',
                        array(
                            'resourceId' => $dropZone->getId()
                        )
                    )
                );
            }
        }

        $allowedTypes = array();
        if ($dropZone->getAllowWorkspaceResource()) $allowedTypes[] = 'resource';
        if ($dropZone->getAllowUpload()) $allowedTypes[] = 'file';
        if ($dropZone->getAllowUrl()) $allowedTypes[] = 'url';
        if ($dropZone->getAllowRichText()) $allowedTypes[] = 'text';

        return array(
            'workspace' => $dropZone->getResourceNode()->getWorkspace(),
            'dropZone' => $dropZone,
            'drop' => $drop,
            'pathArray' => $dropZone->getPathArray(),
            'form' => $form->createView(),
            'allowedTypes' => $allowedTypes
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/document/{documentType}/{dropId}",
     *      name="icap_dropzone_document",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+", "documentType" = "url|file|resource|text"}
     * )
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @ParamConverter("drop", class="ICAPDropZoneBundle:Drop", options={"id" = "dropId"})
     * @Template()
     */
    public function documentAction($dropZone, $user, $documentType, $drop)
    {
        $this->isAllowToOpen($dropZone);

        $formType = null;
        if ($documentType == 'url') {
            if (!$dropZone->getAllowUrl()) {
                throw new AccessDeniedException();
            }
        } else if ($documentType == 'file') {
            if (!$dropZone->getAllowUpload()) {
                throw new AccessDeniedException();
            }
        } else if ($documentType == 'resource') {
            if (!$dropZone->getAllowWorkspaceResource()) {
                throw new AccessDeniedException();
            }
        } else if ($documentType == 'text') {
            if (!$dropZone->getAllowRichText()) {
                throw new AccessDeniedException();
            }
        }
        $form = $this->createForm(new DocumentType(), null, array('documentType' => $documentType));

        if ($this->getRequest()->isMethod('POST')) {
            $form->handleRequest($this->getRequest());

            if ($form->isValid()) {
                $document = new Document();
                if ($documentType == 'url') {
                    $document->setUrl($form->getData()['document']);
                } else if ($documentType == 'file') {
                    $file = $form->getData()['document'];
                    var_dump($file);
                    die();
                } else if ($documentType == 'text') {
                    $text = $form->getData()['document'];


                    throw new \ErrorException();
                } else {
                    throw new \ErrorException();
                }

                $document->setDrop($drop);

                $em = $this->getDoctrine()->getManager();
                $em->persist($document);
                $em->flush();

                return $this->redirect(
                    $this->generateUrl(
                        'icap_dropzone_drop',
                        array(
                            'resourceId' => $dropZone->getId()
                        )
                    )
                );
            }
        }

        $view = 'ICAPDropZoneBundle:DropZone:document.html.twig';
        if ($this->getRequest()->isXMLHttpRequest()) {
            $view = 'ICAPDropZoneBundle:DropZone:documentInline.html.twig';
        }

        return $this->render(
            $view,
            array(
                'workspace' => $dropZone->getResourceNode()->getWorkspace(),
                'dropZone' => $dropZone,
                'drop' => $drop,
                'documentType' => $documentType,
                'pathArray' => $dropZone->getPathArray(),
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/delete/document/{dropId}/{documentId}",
     *      name="icap_dropzone_delete_document",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+", "documentId" = "\d+"}
     * )
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @ParamConverter("drop", class="ICAPDropZoneBundle:Drop", options={"id" = "dropId"})
     * @ParamConverter("document", class="ICAPDropZoneBundle:Document", options={"id" = "documentId"})
     * @Template()
     */
    public function deleteDocumentAction($dropZone, $user, $drop, $document)
    {
        $this->isAllowToOpen($dropZone);

        if ($drop->getId() != $document->getDrop()->getId()) {
            throw new \HttpInvalidParamException();
        }

        if ($drop->getUser()->getId() != $user->getId()) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new DocumentDeleteType(), $document);

        if ($this->getRequest()->isMethod('POST')) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($document);
                $em->flush();

                return $this->redirect(
                    $this->generateUrl(
                        'icap_dropzone_drop',
                        array(
                            'resourceId' => $dropZone->getId()
                        )
                    )
                );
            }
        }

        $view = 'ICAPDropZoneBundle:DropZone:deleteDocument.html.twig';
        if ($this->getRequest()->isXMLHttpRequest()) {
            $view = 'ICAPDropZoneBundle:DropZone:deleteDocumentModal.html.twig';
        }

        return $this->render(
            $view,
            array(
                'workspace' => $dropZone->getResourceNode()->getWorkspace(),
                'dropZone' => $dropZone,
                'drop' => $drop,
                'document' => $document,
                'pathArray' => $dropZone->getPathArray(),
                'form' => $form->createView(),
            )
        );
    }


    private function checkRightToCorrect($dropZone, $user)
    {
        $em = $this->getDoctrine()->getManager();
        // Check that the dropzone is in the process of peer review
        if ($dropZone->isPeerReview() == false) {
            $this->getRequest()->getSession()->getFlashBag()->add('danger', $this->get('translator')->trans('The peer review is not enabled'));

            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_open',
                    array(
                        'resourceId' => $dropZone->getId()
                    )
                )
            );
        }

        // Check that the user has a finished dropzone for this drop.
        $userDrop = $em->getRepository('ICAPDropZoneBundle:Drop')->findOneBy(array(
                'user' => $user,
                'dropZone' => $dropZone,
                'finished' => true
            ));
        if ($userDrop == null) {
            $this->getRequest()->getSession()->getFlashBag()->add('danger', $this->get('translator')->trans('You must have made ​​your copy before correcting'));

            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_open',
                    array(
                        'resourceId' => $dropZone->getId()
                    )
                )
            );
        }

        // Check that the user still make corrections
        $nbCorrection = $em->getRepository('ICAPDropZoneBundle:Correction')->countFinished($dropZone, $user);
        if ($nbCorrection >= $dropZone->getExpectedTotalCorrection()) {
            $this->getRequest()->getSession()->getFlashBag()->add('danger', $this->get('translator')->trans('You no longer have any copies to correct'));

            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_open',
                    array(
                        'resourceId' => $dropZone->getId()
                    )
                )
            );
        }

        return null;
    }

    private function getCorrection($dropZone, $user)
    {
        $em = $this->getDoctrine()->getManager();
        // Check that the user as a not finished correction (exclude admin correction). Otherwise generate a new one.
        $correction = $em->getRepository('ICAPDropZoneBundle:Correction')->getNotFinished($dropZone, $user);
        if ($correction == null) {
            $drop = $em->getRepository('ICAPDropZoneBundle:Drop')->drawDropForCorrection($dropZone, $user);

            if ($drop != null) {
                $correction = new Correction();
                $correction->setDrop($drop);
                $correction->setUser($user);
                $correction->setFinished(false);
                $correction->setDropZone($dropZone);

                $em->persist($correction);
                $em->flush();
            } else {
            }
        } else {
            $correction->setLastOpenDate(new \DateTime());
            $em->persist($correction);
            $em->flush();
        }

        return $correction;
    }

    private function getCriteriaPager($dropZone)
    {
        $em = $this->getDoctrine()->getManager();
        $criterionRepository = $em->getRepository('ICAPDropZoneBundle:Criterion');
        $criterionQuery = $criterionRepository
            ->createQueryBuilder('criterion')
            ->andWhere('criterion.dropZone = :dropZone')
            ->setParameter('dropZone', $dropZone)
            ->orderBy('criterion.id', 'ASC');

        $adapter = new DoctrineORMAdapter($criterionQuery);
        $pager   = new Pagerfanta($adapter);
        $pager->setMaxPerPage(self::CRITERION_PER_PAGE);

        return $pager;
    }

    private function persistGrade($grades, $criterionId, $value, $correction) {
        $em = $this->getDoctrine()->getManager();

        $grade = null;
        $i = 0;
        while ($i < count($grades) and $grade == null) {
            $current = $grades[$i];
            if ($current->getCriterion()->getId() == $criterionId and $current->getCorrection()->getId() == $correction->getId()) {
                $grade = $current;
            }
            $i++;
        }

        if ($grade == null) {
            $criterionReference = $em->getReference('ICAPDropZoneBundle:Criterion', $criterionId);
            $grade = new Grade();
            $grade->setCriterion($criterionReference);
            $grade->setCorrection($correction);
        }
        $grade->setValue($value);
        $em->persist($grade);
        $em->flush();

        return $grade;
    }

    private function endCorrection($dropZone, $correction, $admin)
    {
        $em = $this->getDoctrine()->getManager();

        $correction->setEndDate(new \DateTime());
        $correction->setFinished(true);
        $this->calculateCorrectionTotalGrade($dropZone, $correction);

        $em->persist($correction);
        $em->flush();

        $this->getRequest()->getSession()->getFlashBag()->add('success', $this->get('translator')->trans('Your correction has been saved'));

        if ($admin === true) {
            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_drops_detail',
                    array(
                        'resourceId' => $dropZone->getId(),
                        'dropId' => $correction->getDrop()->getId()
                    )
                )
            );
        } else {
            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_open',
                    array(
                        'resourceId' => $dropZone->getId()
                    )
                )
            );
        }

    }

    private function calculateCorrectionTotalGrade($dropZone, $correction)
    {
        $correction->setTotalGrade(null);

        $nbCriteria = count($dropZone->getPeerReviewCriteria());
        $maxGrade = $dropZone->getTotalCriteriaColumn()-1;
        $sumGrades = 0;
        foreach($correction->getGrades() as $grade) {
            ($grade->getValue() > $maxGrade) ? $sumGrades += $maxGrade : $sumGrades += $grade->getValue();
        }

        $totalGrade = 0;
        if ($nbCriteria != 0) {

            $totalGrade = $sumGrades / ($nbCriteria);
            $totalGrade = ($totalGrade * 20) / ($maxGrade);
        }

        $correction->setTotalGrade($totalGrade);
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
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function correctAction($dropZone, $user, $page)
    {
        $this->isAllowToOpen($dropZone);
        $em = $this->getDoctrine()->getManager();

        $check = $this->checkRightToCorrect($dropZone, $user);
        if ($check !== null) {
            return $check;
        }

        $correction = $this->getCorrection($dropZone, $user);
        if ($correction === null) {
            $this->getRequest()->getSession()->getFlashBag()->add('danger', $this->get('translator')->trans('Unfortunately there is no copy to correct for the moment. Please try again later'));

            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_open',
                    array(
                        'resourceId' => $dropZone->getId()
                    )
                )
            );
        }

        $pager = $this->getCriteriaPager($dropZone);
        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $oldData = array();
        $grades = array();
        if ($correction !== null) {
            $grades = $em->getRepository('ICAPDropZoneBundle:Grade')->findByCriteriaAndCorrection($pager->getCurrentPageResults(), $correction);
            foreach($grades as $grade) {
                $oldData[$grade->getCriterion()->getId()] = ($grade->getValue() >= $dropZone->getTotalCriteriaColumn()) ? ($dropZone->getTotalCriteriaColumn()-1) : $grade->getValue();
            }
        }

        $form = $this->createForm(new CorrectCriteriaPageType(), $oldData, array('criteria' => $pager->getCurrentPageResults(), 'totalChoice' => $dropZone->getTotalCriteriaColumn()));

        if ($this->getRequest()->isMethod('POST') and $correction !== null) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid()) {
                $data = $form->getData();

                foreach ($data as $criterionId => $value) {
                    $this->persistGrade($grades, $criterionId, $value, $correction);
                }

                if ($pager->getCurrentPage() < $pager->getNbPages()) {
                    return $this->redirect(
                        $this->generateUrl(
                            'icap_dropzone_correct_paginated',
                            array(
                                'resourceId' => $dropZone->getId(),
                                'page' => ($page+1)
                            )
                        )
                    );
                } else {
                    if ($dropZone->getAllowCommentInCorrection()) {
                        return $this->redirect(
                            $this->generateUrl(
                                'icap_dropzone_correct_comment',
                                array(
                                    'resourceId' => $dropZone->getId()
                                )
                            )
                        );
                    } else {
                        return $this->endCorrection($dropZone, $correction, false);
                    }
                }
            }
        }

        $view = 'ICAPDropZoneBundle:DropZone:correctCriteria.html.twig';

        return $this->render(
            $view,
            array(
                'workspace' => $dropZone->getResourceNode()->getWorkspace(),
                'dropZone' => $dropZone,
                'correction' => $correction,
                'pager' => $pager,
                'pathArray' => $dropZone->getPathArray(),
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
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function correctCommentAction($dropZone, $user)
    {
        $this->isAllowToOpen($dropZone);
        if ($dropZone->getAllowCommentInCorrection() == false) {
            throw new NotFoundHttpException();
        }
        $check = $this->checkRightToCorrect($dropZone, $user);
        if ($check !== null) {
            return $check;
        }

        $correction = $this->getCorrection($dropZone, $user);
        if ($correction === null) {
            $this->getRequest()->getSession()->getFlashBag()->add('danger', $this->get('translator')->trans('Unfortunately there is no copy to correct for the moment. Please try again later'));

            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_open',
                    array(
                        'resourceId' => $dropZone->getId()
                    )
                )
            );
        }

        $pager = $this->getCriteriaPager($dropZone);
        $form = $this->createForm(new CorrectionCommentType(), $correction);

        if ($this->getRequest()->isMethod('POST')) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid()) {
                $correction = $form->getData();
                return $this->endCorrection($dropZone, $correction, false);
            }
        }

        $view = 'ICAPDropZoneBundle:DropZone:correctComment.html.twig';

        return $this->render(
            $view,
            array(
                'workspace' => $dropZone->getResourceNode()->getWorkspace(),
                'dropZone' => $dropZone,
                'correction' => $correction,
                'pathArray' => $dropZone->getPathArray(),
                'form' => $form->createView(),
                'nbPages' => $pager->getNbPages(),
                'admin' => false,
                'edit' => true
            )
        );
    }

    private function addDropsStats($dropZone, $array)
    {
        $array['nbDropCorrected'] = $this->getDoctrine()->getManager()->getRepository('ICAPDropZoneBundle:Drop')->countDropsFullyCorrected($dropZone);
        $array['nbDrop'] = $this->getDoctrine()->getManager()->getRepository('ICAPDropZoneBundle:Drop')->countDrops($dropZone);

        return $array;
    }

    /**
     * @Route(
     *      "/{resourceId}/drops",
     *      name="icap_dropzone_drops",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @Route(
     *      "/{resourceId}/drops/by/user",
     *      name="icap_dropzone_drops_by_user",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @Route(
     *      "/{resourceId}/drops/by/user/{page}",
     *      name="icap_dropzone_drops_by_user_paginated",
     *      requirements={"resourceId" = "\d+", "page" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @Template()
     */
    public function dropsByUserAction($dropZone, $page)
    {
        $this->isAllowToOpen($dropZone);
        $this->isAllowToEdit($dropZone);

        $dropRepo = $this->getDoctrine()->getManager()->getRepository('ICAPDropZoneBundle:Drop');
        $dropsQuery = $dropRepo->getDropsFullyCorrectedOrderByUserQuery($dropZone);

        $adapter = new DoctrineORMAdapter($dropsQuery);
        $pager   = new Pagerfanta($adapter);
        $pager->setMaxPerPage(self::DROP_PER_PAGE);
        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            if ($page > 0) {
                return $this->redirect(
                    $this->generateUrl(
                        'icap_dropzone_drops_by_user_paginated',
                        array(
                            'resourceId' => $dropZone->getId(),
                            'page' => $pager->getNbPages()
                        )
                    )
                );
            } else {
                throw new NotFoundHttpException();
            }
        }

        return $this->addDropsStats($dropZone, array(
            'workspace' => $dropZone->getResourceNode()->getWorkspace(),
            'dropZone' => $dropZone,
            'pathArray' => $dropZone->getPathArray(),
            'pager' => $pager
        ));
    }

    /**
     * @Route(
     *      "/{resourceId}/drops/by/date",
     *      name="icap_dropzone_drops_by_date",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @Route(
     *      "/{resourceId}/drops/by/date/{page}",
     *      name="icap_dropzone_drops_by_date_paginated",
     *      requirements={"resourceId" = "\d+", "page" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @Template()
     */
    public function dropsByDateAction($dropZone, $page)
    {
        $this->isAllowToOpen($dropZone);
        $this->isAllowToEdit($dropZone);

        $dropRepo = $this->getDoctrine()->getManager()->getRepository('ICAPDropZoneBundle:Drop');
        $dropsQuery = $dropRepo->getDropsFullyCorrectedOrderByDropDateQuery($dropZone);

        $adapter = new DoctrineORMAdapter($dropsQuery);
        $pager   = new Pagerfanta($adapter);
        $pager->setMaxPerPage(self::DROP_PER_PAGE);
        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            if ($page > 0) {
                return $this->redirect(
                    $this->generateUrl(
                        'icap_dropzone_drops_by_date_paginated',
                        array(
                            'resourceId' => $dropZone->getId(),
                            'page' => $pager->getNbPages()
                        )
                    )
                );
            } else {
                throw new NotFoundHttpException();
            }
        }

        return $this->addDropsStats($dropZone, array(
            'workspace' => $dropZone->getResourceNode()->getWorkspace(),
            'dropZone' => $dropZone,
            'pathArray' => $dropZone->getPathArray(),
            'pager' => $pager
        ));
    }

    /**
     * @Route(
     *      "/{resourceId}/drops/awaiting",
     *      name="icap_dropzone_drops_awaiting",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @Route(
     *      "/{resourceId}/drops/awaiting/{page}",
     *      name="icap_dropzone_drops_awaiting_paginated",
     *      requirements={"resourceId" = "\d+", "page" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @Template()
     */
    public function dropsAwaitingAction($dropZone, $page)
    {
        $this->isAllowToOpen($dropZone);
        $this->isAllowToEdit($dropZone);

        $dropRepo = $this->getDoctrine()->getManager()->getRepository('ICAPDropZoneBundle:Drop');
        $dropsQuery = $dropRepo->getDropsAwaitingCorrectionQuery($dropZone);

        $adapter = new DoctrineORMAdapter($dropsQuery);
        $pager   = new Pagerfanta($adapter);
        $pager->setMaxPerPage(self::DROP_PER_PAGE);
        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            if ($page > 0) {
                return $this->redirect(
                    $this->generateUrl(
                        'icap_dropzone_drops_awaiting_paginated',
                        array(
                            'resourceId' => $dropZone->getId(),
                            'page' => $pager->getNbPages()
                        )
                    )
                );
            } else {
                throw new NotFoundHttpException();
            }
        }

        return $this->addDropsStats($dropZone, array(
                'workspace' => $dropZone->getResourceNode()->getWorkspace(),
                'dropZone' => $dropZone,
                'pathArray' => $dropZone->getPathArray(),
                'pager' => $pager
            ));
    }

    /**
     * @Route(
     *      "/{resourceId}/drops/delete/{dropId}/{tab}/{page}",
     *      name="icap_dropzone_drops_delete",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+", "tab" = "\d+", "page" = "\d+"},
     *      defaults={"page" = 1}
     * )
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @ParamConverter("drop", class="ICAPDropZoneBundle:Drop", options={"id" = "dropId"})
     * @Template()
     */
    public function dropsDeleteAction($dropZone, $drop, $tab, $page)
    {
        $this->isAllowToOpen($dropZone);
        $this->isAllowToEdit($dropZone);

        $form = $this->createForm(new DropType(), $drop);

        $previousPath = 'icap_dropzone_drops_by_user_paginated';
        if($tab == 1) {
            $previousPath = 'icap_dropzone_drops_by_date_paginated';
        } elseif ($tab == 2) {
            $previousPath = 'icap_dropzone_drops_awaiting_paginated';
        }

        if ($this->getRequest()->isMethod('POST')) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($drop);
                $em->flush();

                return $this->redirect(
                    $this->generateUrl(
                        $previousPath,
                        array(
                            'resourceId' => $dropZone->getId(),
                            'page' => $page
                        )
                    )
                );
            }
        }

        $view = 'ICAPDropZoneBundle:DropZone:dropsDelete.html.twig';
        if ($this->getRequest()->isXmlHttpRequest()) {
            $view = 'ICAPDropZoneBundle:DropZone:dropsDeleteModal.html.twig';
        }

        return $this->render($view, array(
            'workspace' => $dropZone->getResourceNode()->getWorkspace(),
            'dropZone' => $dropZone,
            'pathArray' => $dropZone->getPathArray(),
            'drop' => $drop,
            'form' => $form->createView(),
            'previousPath' => $previousPath,
            'tab' => $tab,
            'page' => $page
        ));
    }

    /**
     * @Route(
     *      "/{resourceId}/drops/detail/{dropId}",
     *      name="icap_dropzone_drops_detail",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+"}
     * )
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function dropsDetailAction($dropZone, $dropId, $user)
    {
        $this->isAllowToOpen($dropZone);
        $this->isAllowToEdit($dropZone);

        $drop = $this->getDoctrine()->getRepository('ICAPDropZoneBundle:Drop')->getDropAndCorrectionsAndDocumentsAndUser($dropZone, $dropId);

        return array(
            'workspace' => $dropZone->getResourceNode()->getWorkspace(),
            'dropZone' => $dropZone,
            'pathArray' => $dropZone->getPathArray(),
            'drop' => $drop,
            'user' => $user
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
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @Template()
     */
    public function dropsDetailCorrectionAction($dropZone, $state, $correctionId, $page)
    {
        $this->isAllowToOpen($dropZone);
        $this->isAllowToEdit($dropZone);

        $correction = $this->getDoctrine()->getRepository('ICAPDropZoneBundle:Correction')->getCorrectionAndDropAndUserAndDocuments($dropZone, $correctionId);

        $edit = $state == 'edit';

        if ($edit === true and $correction->getEditable() === false) {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();

        $pager = $this->getCriteriaPager($dropZone);
        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $oldData = array();
        $grades = array();
        if ($correction !== null) {
            $grades = $em->getRepository('ICAPDropZoneBundle:Grade')->findByCriteriaAndCorrection($pager->getCurrentPageResults(), $correction);
            foreach($grades as $grade) {
                $oldData[$grade->getCriterion()->getId()] = ($grade->getValue() >= $dropZone->getTotalCriteriaColumn()) ? ($dropZone->getTotalCriteriaColumn()-1) : $grade->getValue();
            }
        }

        $form = $this->createForm(new CorrectCriteriaPageType(), $oldData, array('edit' => $edit, 'criteria' => $pager->getCurrentPageResults(), 'totalChoice' => $dropZone->getTotalCriteriaColumn()));

        if ($edit) {
            if ($this->getRequest()->isMethod('POST') and $correction !== null) {
                $form->handleRequest($this->getRequest());
                if ($form->isValid()) {
                    $data = $form->getData();

                    foreach ($data as $criterionId => $value) {
                        $this->persistGrade($grades, $criterionId, $value, $correction);
                    }

                    if ($correction->getFinished()) {
                        $this->calculateCorrectionTotalGrade($dropZone, $correction);

                        $em->persist($correction);
                        $em->flush();
                    }

                    if ($pager->getCurrentPage() < $pager->getNbPages()) {
                        return $this->redirect(
                            $this->generateUrl(
                                'icap_dropzone_drops_detail_correction_paginated',
                                array(
                                    'resourceId' => $dropZone->getId(),
                                    'state' => 'edit',
                                    'correctionId' => $correction->getId(),
                                    'page' => ($page+1)
                                )
                            )
                        );
                    } else {
                        if ($dropZone->getAllowCommentInCorrection()) {
                            return $this->redirect(
                                $this->generateUrl(
                                    'icap_dropzone_drops_detail_correction_comment',
                                    array(
                                        'resourceId' => $dropZone->getId(),
                                        'state' => 'edit',
                                        'correctionId' => $correction->getId()
                                    )
                                )
                            );
                        } else {
                            return $this->endCorrection($dropZone, $correction, true);
                        }
                    }
                }
            }
        }

        $view = 'ICAPDropZoneBundle:DropZone:correctCriteria.html.twig';

        return $this->render(
            $view,
            array(
                'workspace' => $dropZone->getResourceNode()->getWorkspace(),
                'dropZone' => $dropZone,
                'correction' => $correction,
                'pager' => $pager,
                'pathArray' => $dropZone->getPathArray(),
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
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @Template()
     */
    public function dropsDetailCorrectionCommentAction($dropZone, $state, $correctionId)
    {
        $this->isAllowToOpen($dropZone);
        $this->isAllowToEdit($dropZone);

        $correction = $this->getDoctrine()->getRepository('ICAPDropZoneBundle:Correction')->getCorrectionAndDropAndUserAndDocuments($dropZone, $correctionId);

        $edit = $state == 'edit';

        if ($edit === true and $correction->getEditable() === false) {
            throw new AccessDeniedException();
        }

        $pager = $this->getCriteriaPager($dropZone);
        $form = $this->createForm(new CorrectionCommentType(), $correction, array('edit' => $edit));

        if ($edit) {
            if ($this->getRequest()->isMethod('POST')) {
                $form->handleRequest($this->getRequest());
                if ($form->isValid()) {
                    $correction = $form->getData();
                    return $this->endCorrection($dropZone, $correction, true);
                }
            }
        }

        $view = 'ICAPDropZoneBundle:DropZone:correctComment.html.twig';

        return $this->render(
            $view,
            array(
                'workspace' => $dropZone->getResourceNode()->getWorkspace(),
                'dropZone' => $dropZone,
                'correction' => $correction,
                'pathArray' => $dropZone->getPathArray(),
                'form' => $form->createView(),
                'nbPages' => $pager->getNbPages(),
                'admin' => true,
                'edit' => $edit,
                'state' => $state
            )
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/drops/detail/{dropId}/add/correction",
     *      name="icap_dropzone_drops_detail_add_correction",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+"}
     * )
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @ParamConverter("drop", class="ICAPDropZoneBundle:Drop", options={"id" = "dropId"})
     * @Template()
     */
    public function dropsDetailAddCorrectionAction($dropZone, $user, $drop)
    {
        $this->isAllowToOpen($dropZone);
        $this->isAllowToEdit($dropZone);

        $em = $this->getDoctrine()->getManager();
        $correction = new Correction();
        $correction->setUser($user);
        $correction->setDropZone($dropZone);
        $correction->setDrop($drop);
        //Allow admins to edit this correction
        $correction->setEditable(true);
        $em->persist($correction);
        $em->flush();

        return $this->redirect(
            $this->generateUrl(
                'icap_dropzone_drops_detail_correction',
                array(
                    'resourceId' => $dropZone->getId(),
                    'state' => 'edit',
                    'correctionId' => $correction->getId(),
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
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @ParamConverter("correction", class="ICAPDropZoneBundle:Correction", options={"id" = "correctionId"})
     * @Template()
     */
    public function setCorrectionValidationAction($dropZone, $correction, $value)
    {
        $this->isAllowToOpen($dropZone);
        $this->isAllowToEdit($dropZone);

        $em = $this->getDoctrine()->getManager();

        if ($value == 'yes') {
            $correction->setValid(true);
        } else {
            $correction->setValid(false);
        }

        $em->persist($correction);
        $em->flush();

        return $this->redirect(
            $this->generateUrl(
                'icap_dropzone_drops_detail',
                array(
                    'resourceId' => $dropZone->getId(),
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
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @ParamConverter("drop", class="ICAPDropZoneBundle:Drop", options={"id" = "dropId"})
     * @Template()
     */
    public function invalidateAllCorrectionsAction($dropZone, $drop)
    {
        $this->isAllowToOpen($dropZone);
        $this->isAllowToEdit($dropZone);

        $this->getDoctrine()->getRepository('ICAPDropZoneBundle:Correction')->invalidateAllCorrectionForADrop($dropZone, $drop);

        return $this->redirect(
            $this->generateUrl(
                'icap_dropzone_drops_detail',
                array(
                    'resourceId' => $dropZone->getId(),
                    'dropId' => $drop->getId(),
                )
            )
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/report/drop/{dropId}/{correctionId}",
     *      name="icap_dropzone_report_drop",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+", "correctionId" = "\d+"}
     * )
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @ParamConverter("drop", class="ICAPDropZoneBundle:Drop", options={"id" = "dropId"})
     * @ParamConverter("correction", class="ICAPDropZoneBundle:Correction", options={"id" = "correctionId"})
     * @Template()
     */
    public function reportDropAction($dropZone, $drop, $correction)
    {
        $this->isAllowToOpen($dropZone);

        $form = $this->createForm(new CorrectionReportType(), $correction);

        if ($this->getRequest()->isMethod('POST')) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid()) {
                $drop->setReported(true);
                $correction->setReporter(true);
                $correction->setEndDate(new \DateTime());
                $correction->setFinished(true);
                $correction->setTotalGrade(0);

                $em = $this->getDoctrine()->getManager();
                $em->persist($drop);
                $em->persist($correction);
                $em->flush();

                $this->getRequest()->getSession()->getFlashBag()->add('success', $this->get('translator')->trans('Your report has been saved'));

                return $this->redirect(
                    $this->generateUrl(
                        'icap_dropzone_open',
                        array(
                            'resourceId' => $dropZone->getId()
                        )
                    )
                );
            }
        }

        $view = 'ICAPDropZoneBundle:DropZone:reportDrop.html.twig';
        if ($this->getRequest()->isXmlHttpRequest()) {
            $view = 'ICAPDropZoneBundle:DropZone:reportDropModal.html.twig';
        }

        return $this->render($view, array(
            'workspace' => $dropZone->getResourceNode()->getWorkspace(),
            'dropZone' => $dropZone,
            'drop' => $drop,
            'correction' => $correction,
            'pathArray' => $dropZone->getPathArray(),
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route(
     *      "/{resourceId}/remove/report/{dropId}/{state}/{correctionId}/{invalidate}",
     *      name="icap_dropzone_remove_report",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+", "correctionId" = "\d+", "state" = "show|edit", "invalidate" = "0|1"}
     * )
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @ParamConverter("drop", class="ICAPDropZoneBundle:Drop", options={"id" = "dropId"})
     * @ParamConverter("correction", class="ICAPDropZoneBundle:Correction", options={"id" = "correctionId"})
     * @Template()
     */
    public function removeReportAction($dropZone, $drop, $correction, $state, $invalidate)
    {
        $this->isAllowToOpen($dropZone);
        $this->isAllowToEdit($dropZone);

        $em = $this->getDoctrine()->getManager();
        $correction->setReporter(false);

        if ($invalidate == 1) {
            $correction->setValid(false);
        }

        $em->persist($correction);
        $em->flush();

        $correctionRepo = $this->getDoctrine()->getRepository('ICAPDropZoneBundle:Correction');
        if ($correctionRepo->countReporter($dropZone, $drop) == 0) {
            $drop->setReported(false);
            $em->persist($drop);
            $em->flush();
        }

        return $this->redirect(
            $this->generateUrl(
                'icap_dropzone_drops_detail',
                array(
                    'resourceId' => $dropZone->getId(),
                    'dropId' => $drop->getId(),
                )
            )
        );
    }


    /**
     * @Route(
     *      "/{resourceId}/recalculate/score/{correctionId}",
     *      name="icap_dropzone_remove_report",
     *      requirements={"resourceId" = "\d+", "correctionId" = "\d+"}
     * )
     * @ParamConverter("dropZone", class="ICAPDropZoneBundle:DropZone", options={"id" = "resourceId"})
     * @ParamConverter("correction", class="ICAPDropZoneBundle:Correction", options={"id" = "correctionId"})
     * @Template()
     */
    public function recalculateScoreAction($dropZone, $correction)
    {
        $this->isAllowToOpen($dropZone);
        $this->isAllowToEdit($dropZone);

        $this->calculateCorrectionTotalGrade($dropZone, $correction);
        $em = $this->getDoctrine()->getManager();

        $em->persist($correction);
        $em->flush();

        return $this->redirect(
            $this->generateUrl(
                'icap_dropzone_drops_detail',
                array(
                    'resourceId' => $dropZone->getId(),
                    'dropId' => $correction->getDrop()->getId(),
                )
            )
        );
    }
}