<?php
/**
 * Created by : Vincent SAISSET
 * Date: 22/08/13
 * Time: 09:30.
 */

namespace Innova\CollecticielBundle\Controller;

use Innova\CollecticielBundle\Entity\Criterion;
use Innova\CollecticielBundle\Entity\Dropzone;
use Innova\CollecticielBundle\Event\Log\LogCriterionCreateEvent;
use Innova\CollecticielBundle\Event\Log\LogCriterionDeleteEvent;
use Innova\CollecticielBundle\Event\Log\LogCriterionUpdateEvent;
use Innova\CollecticielBundle\Form\CriterionDeleteType;
use Innova\CollecticielBundle\Form\CriterionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class CriterionController extends DropzoneBaseController
{
    /**
     * @Route(
     *      "/{resourceId}/edit/addcriterion/{page}/criterion/{criterionId}/admin/adminInnova}/collecticiel/{collecticielOpenOrNot}",
     *      name="innova_collecticiel_edit_add_criterion",
     *      requirements={"resourceId" = "\d+", "criterionId" = "\d+", "page" = "\d+", "adminInnova" = "[0-1]", "collecticielOpenOrNot" = "[0-1]"},
     *      defaults={"criterionId" = 0}
     * )
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     * @Template()
     */
    public function editAddCriterionAction($dropzone, $page, $criterionId, $adminInnova, $collecticielOpenOrNot)
    {
        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('innova.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $criterion = new Criterion();
        if ($criterionId != 0) {
            $criterion = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('InnovaCollecticielBundle:Criterion')
                ->find($criterionId);
        } else {
            $criterion->setDropzone($dropzone);
        }

        $form = $this->createForm(new CriterionType(), $criterion);

        if ($this->getRequest()->isXMLHttpRequest()) {
            return $this->render(
                'InnovaCollecticielBundle:Criterion:editAddCriterionModal.html.twig',
                array(
                    'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                    '_resource' => $dropzone,
                    'dropzone' => $dropzone,
                    'form' => $form->createView(),
                    'criterion' => $criterion,
                    'page' => $page,
                )
            );
        }

        return array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'form' => $form->createView(),
            'criterion' => $criterion,
            'page' => $page,
            'adminInnova' => $adminInnova,
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/edit/createcriterion/{page}/{criterionId}",
     *      name="innova_collecticiel_edit_create_criterion",
     *      requirements={"resourceId" = "\d+", "criterionId" = "\d+", "page" = "\d+"},
     *      defaults={"criterionId" = 0}
     * )
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     * @Template("InnovaCollecticielBundle:Dropzone:editAddCriteria.html.twig")
     */
    public function editCreateCriterionAction($dropzone, $page, $criterionId)
    {
        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('innova.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $edit = null;
        $criterion = new Criterion();
        if ($criterionId != 0) {
            $criterion = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('InnovaCollecticielBundle:Criterion')
                ->find($criterionId);
            $edit = true;
        } else {
            $criterion->setDropzone($dropzone);
            $edit = false;
        }

        $form = $this->createForm(new CriterionType(), $criterion);
        $form->handleRequest($this->getRequest());

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
            if ($edit === true) {
                $event = new LogCriterionUpdateEvent($dropzone, $dropzoneChangeSet, $criterion, $criterionChangeSet);
            } else {
                $event = new LogCriterionCreateEvent($dropzone, $dropzoneChangeSet, $criterion);
            }

            $this->dispatch($event);

            return $this->redirect(
                $this->generateUrl(
                    'innova_collecticiel_edit_criteria_paginated',
                    array(
                        'resourceId' => $dropzone->getId(),
                        'page' => $page,
                    )
                )
            );
        }

        return array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'form' => $form->createView(),
            'criterion' => $criterion,
            'page' => $page,
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/edit/deletecriterion/{page}/{criterionId}/{number}",
     *      name="innova_collecticiel_edit_delete_criterion",
     *      requirements={"resourceId" = "\d+", "criterionId" = "\d+", "page" = "\d+", "number" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("criterion", class="InnovaCollecticielBundle:Criterion", options={"id" = "criterionId"})
     * @Template()
     */
    public function editDeleteCriterionAction(Dropzone $dropzone, $page, $criterion, $number)
    {
        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('innova.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $form = $this->createForm(new CriterionDeleteType(), $criterion);

        $nbCorrection = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('InnovaCollecticielBundle:Correction')
            ->countByDropzone($dropzone->getId());

        if ($this->getRequest()->isXMLHttpRequest()) {
            return $this->render(
                'InnovaCollecticielBundle:Criterion:editDeleteCriterionModal.html.twig',
                array(
                    'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                    '_resource' => $dropzone,
                    'dropzone' => $dropzone,
                    'criterion' => $criterion,
                    'form' => $form->createView(),
                    'page' => $page,
                    'number' => $number,
                    'nbCorrection' => $nbCorrection,
                )
            );
        }

        return array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'criterion' => $criterion,
            'form' => $form->createView(),
            'page' => $page,
            'number' => $number,
            'nbCorrection' => $nbCorrection,
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/edit/removecriterion/{page}/{criterionId}",
     *      name="innova_collecticiel_edit_remove_criterion",
     *      requirements={"resourceId" = "\d+", "criterionId" = "\d+", "page" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("criterion", class="InnovaCollecticielBundle:Criterion", options={"id" = "criterionId"})
     * @Template("InnovaCollecticielBundle:Dropzone:editDeleteCriterion.html.twig")
     */
    public function editRemoveCriterionAction(Dropzone $dropzone, $page, Criterion $criterion)
    {
        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->get('innova.manager.dropzone_voter')->isAllowToEdit($dropzone);

        $form = $this->createForm(new CriterionDeleteType(), $criterion);
        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $criterion = $form->getData();
            $criterion->setDropzone($dropzone);

            $em = $this->getDoctrine()->getManager();
            $em->remove($criterion);
            $em->flush();

            $event = new LogCriterionDeleteEvent($dropzone, $criterion);
            $this->dispatch($event);

            if ($dropzone->hasCriteria() === false) {
                $this->getRequest()->getSession()->getFlashBag()->add(
                    'warning',
                    $this->get('translator')->trans('Warning your peer review offers no criteria on which to base correct copies', array(), 'innova_collecticiel')
                );
            }

            return $this->redirect(
                $this->generateUrl(
                    'innova_collecticiel_edit_criteria_paginated',
                    array(
                        'resourceId' => $dropzone->getId(),
                        'page' => $page,
                    )
                )
            );
        }

        return array(
            'workspace' => $dropzone->getResourceNode()->getWorkspace(),
            '_resource' => $dropzone,
            'dropzone' => $dropzone,
            'criterion' => $criterion,
            'form' => $form->createView(),
            'page' => $page,
        );
    }
}
