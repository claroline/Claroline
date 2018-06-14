<?php
/**
 * Created by : Vincent SAISSET
 * Date: 22/08/13
 * Time: 09:30.
 */

namespace Icap\DropzoneBundle\Controller;

use Icap\DropzoneBundle\Entity\Criterion;
use Icap\DropzoneBundle\Entity\Dropzone;
use Icap\DropzoneBundle\Event\Log\LogCriterionCreateEvent;
use Icap\DropzoneBundle\Event\Log\LogCriterionDeleteEvent;
use Icap\DropzoneBundle\Event\Log\LogCriterionUpdateEvent;
use Icap\DropzoneBundle\Form\CriterionDeleteType;
use Icap\DropzoneBundle\Form\CriterionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class CriterionController extends DropzoneBaseController
{
    /**
     * @Route(
     *      "/{resourceId}/edit/addcriterion/{page}/{criterionId}",
     *      name="icap_dropzone_edit_add_criterion",
     *      requirements={"resourceId" = "\d+", "criterionId" = "\d+", "page" = "\d+"},
     *      defaults={"criterionId" = 0}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     */
    public function editAddCriterionAction(Request $request, $dropzone, $page, $criterionId)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $criterion = new Criterion();
        if (0 !== $criterionId) {
            $criterion = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('IcapDropzoneBundle:Criterion')
                ->find($criterionId);
        } else {
            $criterion->setDropzone($dropzone);
        }

        $form = $this->createForm(CriterionType::class, $criterion);

        if ($request->isXMLHttpRequest()) {
            return $this->render(
                'IcapDropzoneBundle:criterion:edit_add_criterion_modal.html.twig',
                [
                    'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                    '_resource' => $dropzone,
                    'dropzone' => $dropzone,
                    'form' => $form->createView(),
                    'criterion' => $criterion,
                    'page' => $page,
                ]
            );
        }

        return [
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'form' => $form->createView(),
            'criterion' => $criterion,
            'page' => $page,
        ];
    }

    /**
     * @Route(
     *      "/{resourceId}/edit/createcriterion/{page}/{criterionId}",
     *      name="icap_dropzone_edit_create_criterion",
     *      requirements={"resourceId" = "\d+", "criterionId" = "\d+", "page" = "\d+"},
     *      defaults={"criterionId" = 0}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @Template("IcapDropzoneBundle:dropzone:edit_add_criteria.html.twig")
     */
    public function editCreateCriterionAction(Request $request, $dropzone, $page, $criterionId)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $edit = null;
        $criterion = new Criterion();
        if (0 !== $criterionId) {
            $criterion = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('IcapDropzoneBundle:Criterion')
                ->find($criterionId);
            $edit = true;
        } else {
            $criterion->setDropzone($dropzone);
            $edit = false;
        }

        $form = $this->createForm(CriterionType::class, $criterion);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $criterion = $form->getData();
            $criterion->setDropzone($dropzone);

            $em = $this->getDoctrine()->getManager();
            $unitOfWork = $em->getUnitOfWork();
            $unitOfWork->computeChangeSets();
            $dropzoneChangeSet = $unitOfWork->getEntityChangeSet($dropzone);
            if ($edit) {
                $criterionChangeSet = $unitOfWork->getEntityChangeSet($criterion);
            }

            $em->persist($criterion);
            $em->persist($dropzone);
            $em->flush();

            $event = null;
            if (true === $edit) {
                $event = new LogCriterionUpdateEvent($dropzone, $dropzoneChangeSet, $criterion, $criterionChangeSet);
            } else {
                $event = new LogCriterionCreateEvent($dropzone, $dropzoneChangeSet, $criterion);
            }

            $this->dispatch($event);

            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_edit_criteria_paginated',
                    [
                        'resourceId' => $dropzone->getId(),
                        'page' => $page,
                    ]
                )
            );
        }

        return [
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'form' => $form->createView(),
            'criterion' => $criterion,
            'page' => $page,
        ];
    }

    /**
     * @Route(
     *      "/{resourceId}/edit/deletecriterion/{page}/{criterionId}/{number}",
     *      name="icap_dropzone_edit_delete_criterion",
     *      requirements={"resourceId" = "\d+", "criterionId" = "\d+", "page" = "\d+", "number" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("criterion", class="IcapDropzoneBundle:Criterion", options={"id" = "criterionId"})
     */
    public function editDeleteCriterionAction(Request $request, Dropzone $dropzone, $page, $criterion, $number)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $form = $this->createForm(CriterionDeleteType::class, $criterion);

        $nbCorrection = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('IcapDropzoneBundle:Correction')
            ->countByDropzone($dropzone->getId());

        if ($request->isXMLHttpRequest()) {
            return $this->render(
                'IcapDropzoneBundle:criterion:edit_delete_criterion_modal.html.twig',
                [
                    'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                    '_resource' => $dropzone,
                    'dropzone' => $dropzone,
                    'criterion' => $criterion,
                    'form' => $form->createView(),
                    'page' => $page,
                    'number' => $number,
                    'nbCorrection' => $nbCorrection,
                ]
            );
        }

        return [
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'criterion' => $criterion,
            'form' => $form->createView(),
            'page' => $page,
            'number' => $number,
            'nbCorrection' => $nbCorrection,
        ];
    }

    /**
     * @Route(
     *      "/{resourceId}/edit/removecriterion/{page}/{criterionId}",
     *      name="icap_dropzone_edit_remove_criterion",
     *      requirements={"resourceId" = "\d+", "criterionId" = "\d+", "page" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("criterion", class="IcapDropzoneBundle:Criterion", options={"id" = "criterionId"})
     * @Template("IcapDropzoneBundle:dropzone:edit_delete_criterion.html.twig")
     */
    public function editRemoveCriterionAction(Request $request, Dropzone $dropzone, $page, Criterion $criterion)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $form = $this->createForm(CriterionDeleteType::class, $criterion);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $criterion = $form->getData();
            $criterion->setDropzone($dropzone);

            $em = $this->getDoctrine()->getManager();
            $em->remove($criterion);
            $em->flush();

            $event = new LogCriterionDeleteEvent($dropzone, $criterion);
            $this->dispatch($event);

            if (false === $dropzone->hasCriteria()) {
                $request->getSession()->getFlashBag()->add(
                    'warning',
                    $this->get('translator')->trans('Warning your peer review offers no criteria on which to base correct copies', [], 'icap_dropzone')
                );
            }

            return $this->redirect(
                $this->generateUrl(
                    'icap_dropzone_edit_criteria_paginated',
                    [
                        'resourceId' => $dropzone->getId(),
                        'page' => $page,
                    ]
                )
            );
        }

        return [
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'criterion' => $criterion,
            'form' => $form->createView(),
            'page' => $page,
        ];
    }
}
