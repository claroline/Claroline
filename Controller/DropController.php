<?php
/**
 * Created by : Vincent SAISSET
 * Date: 22/08/13
 * Time: 09:30
 */

namespace Icap\DropzoneBundle\Controller;

use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Event\Log\LogResourceUpdateEvent;
use Icap\DropzoneBundle\Entity\Correction;
use Icap\DropzoneBundle\Entity\Drop;
use Icap\DropzoneBundle\Entity\Dropzone;
use Icap\DropzoneBundle\Event\Log\LogCorrectionUpdateEvent;
use Icap\DropzoneBundle\Event\Log\LogDropEndEvent;
use Icap\DropzoneBundle\Event\Log\LogDropStartEvent;
use Icap\DropzoneBundle\Form\CorrectionReportType;
use Icap\DropzoneBundle\Form\DropType;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DropController extends DropzoneBaseController
{
    /**
     * @Route(
     *      "/{resourceId}/drop",
     *      name="icap_dropzone_drop",
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
    public function dropAction(Dropzone $dropzone, $user)
    {
        $this->isAllowToOpen($dropzone);

        $em = $this->getDoctrine()->getManager();
        $dropRepo = $em->getRepository('IcapDropzoneBundle:Drop');

        if ($dropRepo->findOneBy(array('dropzone' => $dropzone, 'user' => $user, 'finished' => true)) !== null) {
            $this->getRequest()->getSession()->getFlashBag()->add(
                'error',
                $this->get('translator')->trans('You ve already made ​​your copy for this review', array(), 'icap_dropzone')
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

        $notFinishedDrop = $dropRepo->findOneBy(array('dropzone' => $dropzone, 'user' => $user, 'finished' => false));
        if ($notFinishedDrop === null) {
            $notFinishedDrop = new Drop();
            $number = ($dropRepo->getLastNumber($dropzone) + 1);
            $notFinishedDrop->setNumber($number);

            $notFinishedDrop->setUser($user);
            $notFinishedDrop->setDropzone($dropzone);
            $notFinishedDrop->setFinished(false);

            $em->persist($notFinishedDrop);
            $em->flush();
            $em->refresh($notFinishedDrop);

            $event = new LogDropStartEvent($dropzone, $notFinishedDrop);
            $this->dispatch($event);
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

                $event = new LogDropEndEvent($dropzone, $notFinishedDrop);
                $this->dispatch($event);

                $this->getRequest()->getSession()->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('Your copy has been saved', array(), 'icap_dropzone')
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
        }

        $allowedTypes = array();
        if ($dropzone->getAllowWorkspaceResource()) $allowedTypes[] = 'resource';
        if ($dropzone->getAllowUpload()) $allowedTypes[] = 'file';
        if ($dropzone->getAllowUrl()) $allowedTypes[] = 'url';
        if ($dropzone->getAllowRichText()) $allowedTypes[] = 'text';

        $resourceTypes = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();

        return array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'drop' => $drop,
            'form' => $form->createView(),
            'allowedTypes' => $allowedTypes,
            'resourceTypes' => $resourceTypes
        );
    }

    private function addDropsStats($dropzone, $array)
    {
        $dropRepo = $this->getDoctrine()->getManager()->getRepository('IcapDropzoneBundle:Drop');
        $array['nbDropCorrected'] = $dropRepo->countDropsFullyCorrected($dropzone);
        $array['nbDrop'] = $dropRepo->countDrops($dropzone);

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
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(DropzoneBaseController::DROP_PER_PAGE);
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
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
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
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(DropzoneBaseController::DROP_PER_PAGE);
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
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
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
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(DropzoneBaseController::DROP_PER_PAGE);
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
            '_resource' => $dropzone,
            'dropzone'  => $dropzone,
            'pager'     => $pager,
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
        if ($tab == 1) {
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

        $view = 'IcapDropzoneBundle:Drop:dropsDelete.html.twig';
        if ($this->getRequest()->isXmlHttpRequest()) {
            $view = 'IcapDropzoneBundle:Drop:dropsDeleteModal.html.twig';
        }

        return $this->render($view, array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
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
     * @Template()
     */
    public function dropsDetailAction($dropzone, $dropId)
    {
        $this->isAllowToOpen($dropzone);
        $this->isAllowToEdit($dropzone);

        $drop = $this
            ->getDoctrine()
            ->getRepository('IcapDropzoneBundle:Drop')
            ->getDropAndCorrectionsAndDocumentsAndUser($dropzone, $dropId);

        return array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'drop' => $drop
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

                $this
                    ->getRequest()
                    ->getSession()
                    ->getFlashBag()
                    ->add('success', $this->get('translator')->trans('Your report has been saved', array(), 'icap_dropzone'));

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

        $view = 'IcapDropzoneBundle:Drop:reportDrop.html.twig';
        if ($this->getRequest()->isXmlHttpRequest()) {
            $view = 'IcapDropzoneBundle:Drop:reportDropModal.html.twig';
        }

        return $this->render($view, array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'drop' => $drop,
            'correction' => $correction,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route(
     *      "/{resourceId}/remove/report/{dropId}/{correctionId}/{invalidate}",
     *      name="icap_dropzone_remove_report",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+", "correctionId" = "\d+", "invalidate" = "0|1"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("drop", class="IcapDropzoneBundle:Drop", options={"id" = "dropId"})
     * @ParamConverter("correction", class="IcapDropzoneBundle:Correction", options={"id" = "correctionId"})
     * @Template()
     */
    public function removeReportAction(Dropzone $dropzone, Drop $drop, Correction $correction, $invalidate)
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

        $event = new LogCorrectionUpdateEvent($dropzone, $drop, $correction);
        $this->dispatch($event);

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
}