<?php
/**
 * Created by : Vincent SAISSET
 * Date: 22/08/13
 * Time: 09:30
 */

namespace Icap\DropzoneBundle\Controller;

use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Icap\DropzoneBundle\Entity\Correction;
use Icap\DropzoneBundle\Entity\Criterion;
use Icap\DropzoneBundle\Entity\Document;
use Icap\DropzoneBundle\Entity\Drop;
use Icap\DropzoneBundle\Entity\Dropzone;
use Icap\DropzoneBundle\Entity\Grade;
use Icap\DropzoneBundle\Form\CorrectCriteriaPageType;
use Icap\DropzoneBundle\Form\CorrectionCommentType;
use Icap\DropzoneBundle\Form\CorrectionReportType;
use Icap\DropzoneBundle\Form\CriterionDeleteType;
use Icap\DropzoneBundle\Form\CriterionType;
use Icap\DropzoneBundle\Form\DocumentDeleteType;
use Icap\DropzoneBundle\Form\DocumentType;
use Icap\DropzoneBundle\Form\DropType;
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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class DropzoneController extends Controller {

    const CRITERION_PER_PAGE = 10;
    const DROP_PER_PAGE = 10;

    protected function isAllow($dropzone, $actionName)
    {
        $collection = new ResourceCollection(array($dropzone->getResourceNode()));
        if (false === $this->get('security.context')->isGranted($actionName, $collection)) {
            throw new AccessDeniedException();
        }
    }

    protected function isAllowToEdit($dropzone)
    {
        $this->isAllow($dropzone, 'EDIT');
    }

    protected function isAllowToOpen($dropzone)
    {
        $this->isAllow($dropzone, 'OPEN');
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
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function editCommonAction($dropzone)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

//        $dropzone->setName($dropzone->getResourceNode()->getName());

        $form = $this->createForm(new DropzoneCommonType(), $dropzone);//, array('language' => $this->container->getParameter('locale')));

        if ($this->getRequest()->isMethod('POST')) {
            $form->handleRequest($this->getRequest());

            $dropzone = $form->getData();

            if (!$dropzone->getPeerReview() and $dropzone->getManualState() == 'peerReview') {
                $dropzone->setManualState('notStarted');
            }
            if ($dropzone->getEditionState() < 2) {
                $dropzone->setEditionState(2);
            }

            if (!$dropzone->getDisplayNotationToLearners() and ! $dropzone->getDisplayNotationMessageToLearners()) {
                $form->get('displayNotationToLearners')->addError(new FormError('Choose at least one type of ranking'));
                $form->get('displayNotationMessageToLearners')->addError(new FormError('Choose at least one type of ranking'));
            }

            if (!$dropzone->getAllowWorkspaceResource() and !$dropzone->getAllowUpload() and !$dropzone->getAllowUrl()) {
                $form->get('allowWorkspaceResource')->addError(new FormError('Choose at least one type of document'));
                $form->get('allowUpload')->addError(new FormError('Choose at least one type of document'));
                $form->get('allowUrl')->addError(new FormError('Choose at least one type of document'));
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
                if($dropzone->getStartAllowDrop() !== null && $dropzone->getStartReview() !== null) {
                    if ($dropzone->getStartAllowDrop()->getTimestamp() > $dropzone->getStartReview()->getTimestamp()) {
                        $form->get('startReview')->addError(new FormError('Must be after start allow drop'));
                        $form->get('startAllowDrop')->addError(new FormError('Must be before start peer review'));
                    }
                }
                if($dropzone->getEndAllowDrop() !== null && $dropzone->getEndReview() !== null) {
                    if ($dropzone->getEndAllowDrop()->getTimestamp() > $dropzone->getEndReview()->getTimestamp()) {
                        $form->get('endReview')->addError(new FormError('Must be after end allow drop'));
                        $form->get('endAllowDrop')->addError(new FormError('Must be before end peer review'));
                    }
                }
            }

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($dropzone);
                $em->flush();

                if ($dropzone->getPeerReview()) {
                    return $this->redirect(
                        $this->generateUrl(
                            'icap_dropzone_edit_criteria',
                            array(
                                'resourceId' => $dropzone->getId()
                            )
                        )
                    );
                } else {
                    $this->getRequest()->getSession()->getFlashBag()->add('success', $this->get('translator')->trans('The evaluation has been successfully saved'));
                }
            }
        }

        return array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            'dropzone' => $dropzone,
            'pathArray' => $dropzone->getPathArray(),
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
    public function editCriteriaAction($dropzone, $page)
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
                $em->persist($dropzone);
                $em->flush();

                $this->getRequest()->getSession()->getFlashBag()->add('success', $this->get('translator')->trans('The evaluation has been successfully saved'));
            }
        }

        return array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            'dropzone' => $dropzone,
            'pathArray' => $dropzone->getPathArray(),
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
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function editAddCriterionAction($dropzone, $page, $criterionId)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

        $criterion = new Criterion();
        if ($criterionId != 0) {
            $criterion = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('IcapDropzoneBundle:Criterion')
                ->find($criterionId);
        } else {
            $criterion->setDropzone($dropzone);
        }

        $form = $this->createForm(new CriterionType(), $criterion);

        if ($this->getRequest()->isXMLHttpRequest()) {

            return $this->render(
                'IcapDropzoneBundle:Dropzone:editAddCriterionModal.html.twig',
                array(
                    'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                    'dropzone' => $dropzone,
                    'pathArray' => $dropzone->getPathArray(),
                    'form' => $form->createView(),
                    'criterion' => $criterion,
                    'page' => $page
                )
            );
        }

        return array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            'dropzone' => $dropzone,
            'pathArray' => $dropzone->getPathArray(),
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
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @Template("IcapDropzoneBundle:Dropzone:editAddCriteria.html.twig")
     */
    public function editCreateCriterionAction($dropzone, $page, $criterionId)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);


        $criterion = new Criterion();
        if ($criterionId != 0) {
            $criterion = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('IcapDropzoneBundle:Criterion')
                ->find($criterionId);
        } else {
            $criterion->setDropzone($dropzone);
        }

        $form = $this->createForm(new CriterionType(), $criterion);
        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $criterion = $form->getData();
            $criterion->setDropzone($dropzone);

            $em = $this->getDoctrine()->getManager();
            $em->persist($criterion);
            $em->persist($dropzone);
            $em->flush();

            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_edit_criteria_paginated',
                    array(
                        'resourceId' => $dropzone->getId(),
                        'page' => $page
                    )
                )
            );
        }

        return array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            'dropzone' => $dropzone,
            'pathArray' => $dropzone->getPathArray(),
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
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("criterion", class="IcapDropzoneBundle:Criterion", options={"id" = "criterionId"})
     * @Template()
     */
    public function editDeleteCriterionAction($dropzone, $page, $criterion, $number)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

        $form = $this->createForm(new CriterionDeleteType(), $criterion);


        if ($this->getRequest()->isXMLHttpRequest()) {

            return $this->render(
                'IcapDropzoneBundle:Dropzone:editDeleteCriterionModal.html.twig',
                array(
                    'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                    'dropzone' => $dropzone,
                    'pathArray' => $dropzone->getPathArray(),
                    'criterion' => $criterion,
                    'form' => $form->createView(),
                    'page' => $page,
                    'number' => $number
                )
            );
        }

        return array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            'dropzone' => $dropzone,
            'pathArray' => $dropzone->getPathArray(),
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
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("criterion", class="IcapDropzoneBundle:Criterion", options={"id" = "criterionId"})
     * @Template("IcapDropzoneBundle:Dropzone:editDeleteCriterion.html.twig")
     */
    public function editRemoveCriterionAction($dropzone, $page, $criterion)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

        $form = $this->createForm(new CriterionDeleteType(), $criterion);
        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $criterion = $form->getData();
            $criterion->setDropzone($dropzone);

            $em = $this->getDoctrine()->getManager();
            $em->remove($criterion);
            $em->flush();

            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_edit_criteria_paginated',
                    array(
                        'resourceId' => $dropzone->getId(),
                        'page' => $page
                    )
                )
            );
        }

        return array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            'dropzone' => $dropzone,
            'pathArray' => $dropzone->getPathArray(),
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
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function openAction($dropzone, $user)
    {
        //Participant view for a dropzone
        $this->isAllowToOpen($dropzone);

        $em = $this->getDoctrine()->getManager();
        $dropRepo = $em->getRepository('IcapDropzoneBundle:Drop');
        $drop = $dropRepo->findOneBy(array('dropzone' => $dropzone, 'user' => $user, 'finished' => true));

        $nbCorrections = $this->getDoctrine()->getManager()->getRepository('IcapDropzoneBundle:Correction')->countFinished($dropzone, $user);

        return array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            'pathArray' => $dropzone->getPathArray(),
            'dropzone' => $dropzone,
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
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function dropAction($dropzone, $user)
    {
        $this->isAllowToOpen($dropzone);

        $em = $this->getDoctrine()->getManager();
        $dropRepo = $em->getRepository('IcapDropzoneBundle:Drop');

        if ($dropRepo->findOneBy(array('dropzone' => $dropzone, 'user' => $user, 'finished' => true)) !== null) {
            $this->getRequest()->getSession()->getFlashBag()->add('danger', $this->get('translator')->trans('You ve already made ​​your copy for this review'));

            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_open',
                    array(
                        'resourceId' => $dropzone->getId()
                    )
                )
            );
        }

        $notFinishedDrop = $dropRepo->findOneBy(array('dropzone' => $dropzone, 'user' => $user, 'finished' => false));
        if ($notFinishedDrop === null) {
            $notFinishedDrop = new Drop();
            $notFinishedDrop->setUser($user);
            $notFinishedDrop->setDropzone($dropzone);
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
                            'resourceId' => $dropzone->getId()
                        )
                    )
                );
            }
        }

        $allowedTypes = array();
        if ($dropzone->getAllowWorkspaceResource()) $allowedTypes[] = 'resource';
        if ($dropzone->getAllowUpload()) $allowedTypes[] = 'file';
        if ($dropzone->getAllowUrl()) $allowedTypes[] = 'url';
        if ($dropzone->getAllowRichText()) $allowedTypes[] = 'text';

        return array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            'dropzone' => $dropzone,
            'drop' => $drop,
            'pathArray' => $dropzone->getPathArray(),
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
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @ParamConverter("drop", class="IcapDropzoneBundle:Drop", options={"id" = "dropId"})
     * @Template()
     */
    public function documentAction($dropzone, $user, $documentType, $drop)
    {
        $this->isAllowToOpen($dropzone);

        $formType = null;
        if ($documentType == 'url') {
            if (!$dropzone->getAllowUrl()) {
                throw new AccessDeniedException();
            }
        } else if ($documentType == 'file') {
            if (!$dropzone->getAllowUpload()) {
                throw new AccessDeniedException();
            }
        } else if ($documentType == 'resource') {
            if (!$dropzone->getAllowWorkspaceResource()) {
                throw new AccessDeniedException();
            }
        } else if ($documentType == 'text') {
            if (!$dropzone->getAllowRichText()) {
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
                            'resourceId' => $dropzone->getId()
                        )
                    )
                );
            }
        }

        $view = 'IcapDropzoneBundle:Dropzone:document.html.twig';
        if ($this->getRequest()->isXMLHttpRequest()) {
            $view = 'IcapDropzoneBundle:Dropzone:documentInline.html.twig';
        }

        return $this->render(
            $view,
            array(
                'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                'dropzone' => $dropzone,
                'drop' => $drop,
                'documentType' => $documentType,
                'pathArray' => $dropzone->getPathArray(),
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
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @ParamConverter("drop", class="IcapDropzoneBundle:Drop", options={"id" = "dropId"})
     * @ParamConverter("document", class="IcapDropzoneBundle:Document", options={"id" = "documentId"})
     * @Template()
     */
    public function deleteDocumentAction($dropzone, $user, $drop, $document)
    {
        $this->isAllowToOpen($dropzone);

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
                            'resourceId' => $dropzone->getId()
                        )
                    )
                );
            }
        }

        $view = 'IcapDropzoneBundle:Dropzone:deleteDocument.html.twig';
        if ($this->getRequest()->isXMLHttpRequest()) {
            $view = 'IcapDropzoneBundle:Dropzone:deleteDocumentModal.html.twig';
        }

        return $this->render(
            $view,
            array(
                'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                'dropzone' => $dropzone,
                'drop' => $drop,
                'document' => $document,
                'pathArray' => $dropzone->getPathArray(),
                'form' => $form->createView(),
            )
        );
    }


    private function checkRightToCorrect($dropzone, $user)
    {
        $em = $this->getDoctrine()->getManager();
        // Check that the dropzone is in the process of peer review
        if ($dropzone->isPeerReview() == false) {
            $this->getRequest()->getSession()->getFlashBag()->add('danger', $this->get('translator')->trans('The peer review is not enabled'));

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
            $this->getRequest()->getSession()->getFlashBag()->add('danger', $this->get('translator')->trans('You must have made ​​your copy before correcting'));

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
            $this->getRequest()->getSession()->getFlashBag()->add('danger', $this->get('translator')->trans('You no longer have any copies to correct'));

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
            } else {
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

    private function endCorrection($dropzone, $correction, $admin)
    {
        $em = $this->getDoctrine()->getManager();

        $correction->setEndDate(new \DateTime());
        $correction->setFinished(true);
        $this->calculateCorrectionTotalGrade($dropzone, $correction);

        $em->persist($correction);
        $em->flush();

        $this->getRequest()->getSession()->getFlashBag()->add('success', $this->get('translator')->trans('Your correction has been saved'));

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

    private function calculateCorrectionTotalGrade($dropzone, $correction)
    {
        $correction->setTotalGrade(null);

        $nbCriteria = count($dropzone->getPeerReviewCriteria());
        $maxGrade = $dropzone->getTotalCriteriaColumn()-1;
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
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
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
            $this->getRequest()->getSession()->getFlashBag()->add('danger', $this->get('translator')->trans('Unfortunately there is no copy to correct for the moment. Please try again later'));

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
            $grades = $em->getRepository('IcapDropzoneBundle:Grade')->findByCriteriaAndCorrection($pager->getCurrentPageResults(), $correction);
            foreach($grades as $grade) {
                $oldData[$grade->getCriterion()->getId()] = ($grade->getValue() >= $dropzone->getTotalCriteriaColumn()) ? ($dropzone->getTotalCriteriaColumn()-1) : $grade->getValue();
            }
        }

        $form = $this->createForm(new CorrectCriteriaPageType(), $oldData, array('criteria' => $pager->getCurrentPageResults(), 'totalChoice' => $dropzone->getTotalCriteriaColumn()));

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
                                'resourceId' => $dropzone->getId(),
                                'page' => ($page+1)
                            )
                        )
                    );
                } else {
                    if ($dropzone->getAllowCommentInCorrection()) {
                        return $this->redirect(
                            $this->generateUrl(
                                'icap_dropzone_correct_comment',
                                array(
                                    'resourceId' => $dropzone->getId()
                                )
                            )
                        );
                    } else {
                        return $this->endCorrection($dropzone, $correction, false);
                    }
                }
            }
        }

        $view = 'IcapDropzoneBundle:Dropzone:correctCriteria.html.twig';

        return $this->render(
            $view,
            array(
                'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                'dropzone' => $dropzone,
                'correction' => $correction,
                'pager' => $pager,
                'pathArray' => $dropzone->getPathArray(),
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
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function correctCommentAction($dropzone, $user)
    {
        $this->isAllowToOpen($dropzone);
        if ($dropzone->getAllowCommentInCorrection() == false) {
            throw new NotFoundHttpException();
        }
        $check = $this->checkRightToCorrect($dropzone, $user);
        if ($check !== null) {
            return $check;
        }

        $correction = $this->getCorrection($dropzone, $user);
        if ($correction === null) {
            $this->getRequest()->getSession()->getFlashBag()->add('danger', $this->get('translator')->trans('Unfortunately there is no copy to correct for the moment. Please try again later'));

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
        $form = $this->createForm(new CorrectionCommentType(), $correction);

        if ($this->getRequest()->isMethod('POST')) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid()) {
                $correction = $form->getData();
                return $this->endCorrection($dropzone, $correction, false);
            }
        }

        $view = 'IcapDropzoneBundle:Dropzone:correctComment.html.twig';

        return $this->render(
            $view,
            array(
                'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                'dropzone' => $dropzone,
                'correction' => $correction,
                'pathArray' => $dropzone->getPathArray(),
                'form' => $form->createView(),
                'nbPages' => $pager->getNbPages(),
                'admin' => false,
                'edit' => true
            )
        );
    }

    private function addDropsStats($dropzone, $array)
    {
        $array['nbDropCorrected'] = $this->getDoctrine()->getManager()->getRepository('IcapDropzoneBundle:Drop')->countDropsFullyCorrected($dropzone);
        $array['nbDrop'] = $this->getDoctrine()->getManager()->getRepository('IcapDropzoneBundle:Drop')->countDrops($dropzone);

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
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function dropsByUserAction($dropzone, $page)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

        $dropRepo = $this->getDoctrine()->getManager()->getRepository('IcapDropzoneBundle:Drop');
        $dropsQuery = $dropRepo->getDropsFullyCorrectedOrderByUserQuery($dropzone);

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
                            'resourceId' => $dropzone->getId(),
                            'page' => $pager->getNbPages()
                        )
                    )
                );
            } else {
                throw new NotFoundHttpException();
            }
        }

        return $this->addDropsStats($dropzone, array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            'dropzone' => $dropzone,
            'pathArray' => $dropzone->getPathArray(),
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
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function dropsByDateAction($dropzone, $page)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

        $dropRepo = $this->getDoctrine()->getManager()->getRepository('IcapDropzoneBundle:Drop');
        $dropsQuery = $dropRepo->getDropsFullyCorrectedOrderByDropDateQuery($dropzone);

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
                            'resourceId' => $dropzone->getId(),
                            'page' => $pager->getNbPages()
                        )
                    )
                );
            } else {
                throw new NotFoundHttpException();
            }
        }

        return $this->addDropsStats($dropzone, array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            'dropzone' => $dropzone,
            'pathArray' => $dropzone->getPathArray(),
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
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function dropsAwaitingAction($dropzone, $page)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

        $dropRepo = $this->getDoctrine()->getManager()->getRepository('IcapDropzoneBundle:Drop');
        $dropsQuery = $dropRepo->getDropsAwaitingCorrectionQuery($dropzone);

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
                            'resourceId' => $dropzone->getId(),
                            'page' => $pager->getNbPages()
                        )
                    )
                );
            } else {
                throw new NotFoundHttpException();
            }
        }

        return $this->addDropsStats($dropzone, array(
                'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                'dropzone' => $dropzone,
                'pathArray' => $dropzone->getPathArray(),
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
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("drop", class="IcapDropzoneBundle:Drop", options={"id" = "dropId"})
     * @Template()
     */
    public function dropsDeleteAction($dropzone, $drop, $tab, $page)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

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
                            'resourceId' => $dropzone->getId(),
                            'page' => $page
                        )
                    )
                );
            }
        }

        $view = 'IcapDropzoneBundle:Dropzone:dropsDelete.html.twig';
        if ($this->getRequest()->isXmlHttpRequest()) {
            $view = 'IcapDropzoneBundle:Dropzone:dropsDeleteModal.html.twig';
        }

        return $this->render($view, array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            'dropzone' => $dropzone,
            'pathArray' => $dropzone->getPathArray(),
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
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function dropsDetailAction($dropzone, $dropId, $user)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

        $drop = $this->getDoctrine()->getRepository('IcapDropzoneBundle:Drop')->getDropAndCorrectionsAndDocumentsAndUser($dropzone, $dropId);

        return array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            'dropzone' => $dropzone,
            'pathArray' => $dropzone->getPathArray(),
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
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function dropsDetailCorrectionAction($dropzone, $state, $correctionId, $page)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

        $correction = $this->getDoctrine()->getRepository('IcapDropzoneBundle:Correction')->getCorrectionAndDropAndUserAndDocuments($dropzone, $correctionId);

        $edit = $state == 'edit';

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
            $grades = $em->getRepository('IcapDropzoneBundle:Grade')->findByCriteriaAndCorrection($pager->getCurrentPageResults(), $correction);
            foreach($grades as $grade) {
                $oldData[$grade->getCriterion()->getId()] = ($grade->getValue() >= $dropzone->getTotalCriteriaColumn()) ? ($dropzone->getTotalCriteriaColumn()-1) : $grade->getValue();
            }
        }

        $form = $this->createForm(new CorrectCriteriaPageType(), $oldData, array('edit' => $edit, 'criteria' => $pager->getCurrentPageResults(), 'totalChoice' => $dropzone->getTotalCriteriaColumn()));

        if ($edit) {
            if ($this->getRequest()->isMethod('POST') and $correction !== null) {
                $form->handleRequest($this->getRequest());
                if ($form->isValid()) {
                    $data = $form->getData();

                    foreach ($data as $criterionId => $value) {
                        $this->persistGrade($grades, $criterionId, $value, $correction);
                    }

                    if ($correction->getFinished()) {
                        $this->calculateCorrectionTotalGrade($dropzone, $correction);

                        $em->persist($correction);
                        $em->flush();
                    }

                    if ($pager->getCurrentPage() < $pager->getNbPages()) {
                        return $this->redirect(
                            $this->generateUrl(
                                'icap_dropzone_drops_detail_correction_paginated',
                                array(
                                    'resourceId' => $dropzone->getId(),
                                    'state' => 'edit',
                                    'correctionId' => $correction->getId(),
                                    'page' => ($page+1)
                                )
                            )
                        );
                    } else {
                        if ($dropzone->getAllowCommentInCorrection()) {
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
                        } else {
                            return $this->endCorrection($dropzone, $correction, true);
                        }
                    }
                }
            }
        }

        $view = 'IcapDropzoneBundle:Dropzone:correctCriteria.html.twig';

        return $this->render(
            $view,
            array(
                'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                'dropzone' => $dropzone,
                'correction' => $correction,
                'pager' => $pager,
                'pathArray' => $dropzone->getPathArray(),
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
     * @Template()
     */
    public function dropsDetailCorrectionCommentAction($dropzone, $state, $correctionId)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

        $correction = $this->getDoctrine()->getRepository('IcapDropzoneBundle:Correction')->getCorrectionAndDropAndUserAndDocuments($dropzone, $correctionId);

        $edit = $state == 'edit';

        if ($edit === true and $correction->getEditable() === false) {
            throw new AccessDeniedException();
        }

        $pager = $this->getCriteriaPager($dropzone);
        $form = $this->createForm(new CorrectionCommentType(), $correction, array('edit' => $edit));

        if ($edit) {
            if ($this->getRequest()->isMethod('POST')) {
                $form->handleRequest($this->getRequest());
                if ($form->isValid()) {
                    $correction = $form->getData();
                    return $this->endCorrection($dropzone, $correction, true);
                }
            }
        }

        $view = 'IcapDropzoneBundle:Dropzone:correctComment.html.twig';

        return $this->render(
            $view,
            array(
                'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                'dropzone' => $dropzone,
                'correction' => $correction,
                'pathArray' => $dropzone->getPathArray(),
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
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
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
     *      "/{resourceId}/drops/detail/correction/validation/{value}/{correctionId}",
     *      name="icap_dropzone_drops_detail_correction_validation",
     *      requirements={"resourceId" = "\d+", "correctionId" = "\d+", "value" = "no|yes"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("correction", class="IcapDropzoneBundle:Correction", options={"id" = "correctionId"})
     * @Template()
     */
    public function setCorrectionValidationAction($dropzone, $correction, $value)
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

        $this->getDoctrine()->getRepository('IcapDropzoneBundle:Correction')->invalidateAllCorrectionForADrop($dropzone, $drop);

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
     *      "/{resourceId}/report/drop/{dropId}/{correctionId}",
     *      name="icap_dropzone_report_drop",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+", "correctionId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("drop", class="IcapDropzoneBundle:Drop", options={"id" = "dropId"})
     * @ParamConverter("correction", class="IcapDropzoneBundle:Correction", options={"id" = "correctionId"})
     * @Template()
     */
    public function reportDropAction($dropzone, $drop, $correction)
    {
        $this->isAllowToOpen($dropzone);

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
                            'resourceId' => $dropzone->getId()
                        )
                    )
                );
            }
        }

        $view = 'IcapDropzoneBundle:Dropzone:reportDrop.html.twig';
        if ($this->getRequest()->isXmlHttpRequest()) {
            $view = 'IcapDropzoneBundle:Dropzone:reportDropModal.html.twig';
        }

        return $this->render($view, array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            'dropzone' => $dropzone,
            'drop' => $drop,
            'correction' => $correction,
            'pathArray' => $dropzone->getPathArray(),
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route(
     *      "/{resourceId}/remove/report/{dropId}/{state}/{correctionId}/{invalidate}",
     *      name="icap_dropzone_remove_report",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+", "correctionId" = "\d+", "state" = "show|edit", "invalidate" = "0|1"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("drop", class="IcapDropzoneBundle:Drop", options={"id" = "dropId"})
     * @ParamConverter("correction", class="IcapDropzoneBundle:Correction", options={"id" = "correctionId"})
     * @Template()
     */
    public function removeReportAction($dropzone, $drop, $correction, $state, $invalidate)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

        $em = $this->getDoctrine()->getManager();
        $correction->setReporter(false);

        if ($invalidate == 1) {
            $correction->setValid(false);
        }

        $em->persist($correction);
        $em->flush();

        $correctionRepo = $this->getDoctrine()->getRepository('IcapDropzoneBundle:Correction');
        if ($correctionRepo->countReporter($dropzone, $drop) == 0) {
            $drop->setReported(false);
            $em->persist($drop);
            $em->flush();
        }

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
     *      name="icap_dropzone_remove_report",
     *      requirements={"resourceId" = "\d+", "correctionId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("correction", class="IcapDropzoneBundle:Correction", options={"id" = "correctionId"})
     * @Template()
     */
    public function recalculateScoreAction($dropzone, $correction)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

        $this->calculateCorrectionTotalGrade($dropzone, $correction);
        $em = $this->getDoctrine()->getManager();

        $em->persist($correction);
        $em->flush();

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