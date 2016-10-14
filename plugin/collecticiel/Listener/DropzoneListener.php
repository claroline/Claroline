<?php

namespace Innova\CollecticielBundle\Listener;

use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\PluginOptionsEvent;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\ResourceManager;
use Innova\CollecticielBundle\Entity\Criterion;
use Innova\CollecticielBundle\Entity\Dropzone;
use Innova\CollecticielBundle\Form\DropzoneType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service()
 */
class DropzoneListener
{
    private $container;
    private $httpKernel;
    private $request;
    private $resourceManager;

    /**
     * @DI\InjectParams({
     *     "container"       = @DI\Inject("service_container"),
     *     "httpKernel"      = @DI\Inject("http_kernel"),
     *     "requestStack"    = @DI\Inject("request_stack"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager")
     * })
     */
    public function __construct(
        ContainerInterface $container,
        HttpKernelInterface $httpKernel,
        RequestStack $requestStack,
        ResourceManager $resourceManager
    ) {
        $this->container = $container;
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
        $this->resourceManager = $resourceManager;
    }

    /**
     * @DI\Observe("create_form_innova_collecticiel")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new DropzoneType(), new Dropzone());

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            [
                'form' => $form->createView(),
                'resourceType' => 'innova_collecticiel',
            ]
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_innova_collecticiel")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new DropzoneType(), new Dropzone());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $dropzone = $form->getData();
            $event->setResources([$dropzone]);
        } else {
            $content = $this->container->get('templating')->render(
                'ClarolineCoreBundle:Resource:createForm.html.twig',
                [
                    'form' => $form->createView(),
                    'resourceType' => 'innova_collecticiel',
                ]
            );
            $event->setErrorFormContent($content);
        }
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_innova_collecticiel")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $params = [];
        // Modification ERV (août 2015) InnovaERV
        // suite demande JJQ, voir son document de référence d'août 2015
        // il faut venir sur l'onglet "Mon espace collecticiel" et non plus sur "Drop"
        // Point 5 du document
        $collection = new ResourceCollection([$event->getResource()->getResourceNode()]);
        if (false === $this->container->get('security.authorization_checker')->isGranted('EDIT', $collection)) {
            $params['_controller'] = 'InnovaCollecticielBundle:Drop:drop';
        } else {
            // Modification ERV (août 2015) InnovaERV
        // suite demande JJQ, voir son document de référence d'août 2015
        // il faut venir sur l'onglet "Demandes adressées" et non plus sur "Paramètres"
        // Point 3 du document

            $em = $this->container->get('doctrine.orm.entity_manager');

            $dropzone = $em->getRepository('InnovaCollecticielBundle:Dropzone')
            ->find($event->getResource()->getId());

            // Pour savoir si le collecticiel est ouvert ou pas. InnovaERV
            $dropzoneManager = $this->container->get('innova.manager.dropzone_manager');

            if ($dropzoneManager->collecticielOpenOrNot($dropzone)) {
                $params['_controller'] = 'InnovaCollecticielBundle:Drop:dropsAwaiting';
            } else {
                $params['_controller'] = 'InnovaCollecticielBundle:Dropzone:editCommon';
            }
        }
        $params['resourceId'] = $event->getResource()->getId();
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel
            ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_dropzone_innova_collecticiel")
     *
     * @param CustomActionResourceEvent $event
     */
    public function onOpenCustom(CustomActionResourceEvent $event)
    {
        $dropzone = get_class($event->getResource()) === 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut' ?
            $this->resourceManager->getResourceFromShortcut($event->getResource()->getResourceNode()) :
            $event->getResource();
        $dropzoneVoter = $this->container->get('innova.manager.dropzone_voter');
        $canEdit = $dropzoneVoter->checkEditRight($dropzone);

        if (!$canEdit) {
            $route = $this->container
                ->get('router')
                ->generate(
                    'innova_collecticiel_drop',
                    ['resourceId' => $dropzone->getId()]
                );
        } else {
            $route = $this->container
                ->get('router')
                ->generate(
                    'innova_collecticiel_shared_spaces',
                    ['resourceId' => $dropzone->getId()]
                );
        }

        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("edit_dropzone_innova_collecticiel")
     *
     * @param CustomActionResourceEvent $event
     */
    public function onEdit(CustomActionResourceEvent $event)
    {
        $resource = get_class($event->getResource()) === 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut' ?
            $this->resourceManager->getResourceFromShortcut($event->getResource()->getResourceNode()) :
            $event->getResource();
        $route = $this->container
            ->get('router')
            ->generate(
                'innova_collecticiel_edit',
                ['resourceId' => $resource->getId()]
            );
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    public function onList(CustomActionResourceEvent $event)
    {
        $route = $this->container
            ->get('router')
            ->generate(
                'innova_collecticiel_drops_awaiting',
                ['resourceId' => $event->getResource()->getId()]
        );

        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_innova_collecticiel")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($event->getResource());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("plugin_options_innovacollecticiel")
     *
     * @param PluginOptionsEvent $event
     */
    public function onAdministrate(PluginOptionsEvent $event)
    {
    }

    /**
     * @DI\Observe("copy_innova_collecticiel")
     *
     * @param CopyResourceEvent $event
     */
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
