<?php

namespace Innova\CollecticielBundle\Listener;

use Claroline\CoreBundle\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Event\PluginOptionsEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\Log\LogCreateDelegateViewEvent;

use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Innova\CollecticielBundle\Entity\Criterion;
use Innova\CollecticielBundle\Entity\Dropzone;
use Innova\CollecticielBundle\Form\DropzoneType;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class DropzoneListener extends ContainerAware
{
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new DropzoneType(), new Dropzone());
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'innova_collecticiel'
            )
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->container->get('form.factory')->create(new DropzoneType(), new Dropzone());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $dropzone = $form->getData();
            $event->setResources(array($dropzone));
        } else {
            $content = $this->container->get('templating')->render(
                'ClarolineCoreBundle:Resource:createForm.html.twig',
                array(
                    'form' => $form->createView(),
                    'resourceType' => 'innova_collecticiel'
                )
            );
            $event->setErrorFormContent($content);
        }
        $event->stopPropagation();
    }

    public function onOpen(OpenResourceEvent $event)
    {
        // Modification ERV (août 2015) InnovaERV
        // suite demande JJQ, voir son document de référence d'août 2015
        // il faut venir sur l'onglet "Mon espace collecticiel" et non plus sur "Drop"
        // Point 5 du document
        $collection = new ResourceCollection(array($event->getResource()->getResourceNode()));
        if (false === $this->container->get('security.authorization_checker')->isGranted('EDIT', $collection)) {
            $route = $this->container
                ->get('router')
                ->generate(
                    'innova_collecticiel_drop',
                    array('resourceId' => $event->getResource()->getId())
                );
        } else {
        // Modification ERV (août 2015) InnovaERV
        // suite demande JJQ, voir son document de référence d'août 2015
        // il faut venir sur l'onglet "Demandes adressées" et non plus sur "Paramètres"
        // Point 3 du document
            $route = $this->container
                ->get('router')
                ->generate(
                    'innova_collecticiel_drops_awaiting',
                    array('resourceId' => $event->getResource()->getId())
                );
        }


        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    public function onOpenCustom(CustomActionResourceEvent $event)
    {
        $route = $this->container
            ->get('router')
            ->generate(
                'innova_collecticiel_drop',
                array('resourceId' => $event->getResource()->getId())
            );
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    public function onEdit(CustomActionResourceEvent $event)
    {
        $route = $this->container
            ->get('router')
            ->generate(
                'innova_collecticiel_edit',
                array('resourceId' => $event->getResource()->getId())
            );
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }


    public function onList(CustomActionResourceEvent $event)
    {
        $route = $this->container
            ->get('router')
            ->generate(
                'innova_collecticiel_drops',
                array('resourceId' => $event->getResource()->getId())
            );
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    public function onDelete(DeleteResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($event->getResource());
        $event->stopPropagation();
    }

    public function onAdministrate(PluginOptionsEvent $event)
    {
//        $referenceOptionsList = $this->container
//            ->get('doctrine.orm.entity_manager')
//            ->getRepository('IcapReferenceBundle:ReferenceBankOptions')
//            ->findAll();
//
//        $referenceOptions = null;
//        if ((count($referenceOptionsList)) > 0) {
//            $referenceOptions = $referenceOptionsList[0];
//        } else {
//            $referenceOptions = new ReferenceBankOptions();
//        }
//
//        $form = $this->container->get('form.factory')->create(new ReferenceBankOptionsType(), $referenceOptions);
//        $content = $this->container->get('templating')->render(
//            'IcapReferenceBundle::plugin_options_form.html.twig', array(
//                'form' => $form->createView()
//            )
//        );
//        $response = new Response($content);
//        $event->setResponse($response);
//        $event->stopPropagation();
    }

    public function onCopy(CopyResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        /** @var Dropzone $resource */
        $resource = $event->getResource();

        $newDropzone = new Dropzone();
        $newDropzone->setName($resource->getName());
        $newDropzone->setAllowCommentInCorrection($resource->getAllowCommentInCorrection());
        $newDropzone->setAllowRichText($resource->getAllowRichText());
        $newDropzone->setAllowUpload($resource->getAllowUpload());
        $newDropzone->setAllowUrl($resource->getAllowUrl());
        $newDropzone->setAllowWorkspaceResource($resource->getAllowWorkspaceResource());
        $newDropzone->setDisplayNotationMessageToLearners($resource->getDisplayNotationMessageToLearners());
        $newDropzone->setDisplayNotationToLearners($resource->getDisplayNotationToLearners());
        $newDropzone->setEditionState($resource->getEditionState());
        $newDropzone->setEndAllowDrop($resource->getEndAllowDrop());
        $newDropzone->setEndReview($resource->getEndReview());
        $newDropzone->setExpectedTotalCorrection($resource->getExpectedTotalCorrection());
        $newDropzone->setInstruction($resource->getInstruction());
        $newDropzone->setManualPlanning($resource->getManualPlanning());
        $newDropzone->setManualState($resource->getManualState());
        $newDropzone->setMinimumScoreToPass($resource->getMinimumScoreToPass());
        $newDropzone->setPeerReview($resource->getPeerReview());
        $newDropzone->setStartAllowDrop($resource->getStartAllowDrop());
        $newDropzone->setStartReview($resource->getStartReview());
        $newDropzone->setTotalCriteriaColumn($resource->getTotalCriteriaColumn());

        $oldCriteria = $resource->getPeerReviewCriteria();

        foreach ($oldCriteria as $oldCriterion) {
            $newCriterion = new Criterion();
            $newCriterion->setInstruction($oldCriterion->getInstruction());

            $newDropzone->addCriterion($newCriterion);
        }
        $em->persist($newDropzone);

        $event->setCopy($newDropzone);
        $event->stopPropagation();
    }
}