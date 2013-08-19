<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\SecurityContextInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Form\DirectoryType;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Event\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\Event\ExportDirectoryTemplateEvent;
use Claroline\CoreBundle\Event\Event\ImportResourceTemplateEvent;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Event\StrictDispatcher;

/**
 * @DI\Service
 */
class DirectoryListener
{
    private $container;
    private $roleManager;
    private $resourceManager;
    private $rightsManager;
    private $security;
    private $eventDispatcher;
    private $formFactory;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "rightsManager"   = @DI\Inject("claroline.manager.rights_manager"),
     *     "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "security"        = @DI\Inject("security.context"),
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "templating"      = @DI\Inject("templating"),
     *     "container"       = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        RoleManager $roleManager,
        ResourceManager $resourceManager,
        RightsManager $rightsManager,
        StrictDispatcher $eventDispatcher,
        SecurityContextInterface $security,
        FormFactoryInterface $formFactory,
        TwigEngine $templating,
        ContainerInterface $container
    )
    {
        $this->roleManager = $roleManager;
        $this->resourceManager = $resourceManager;
        $this->rightsManager = $rightsManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->security = $security;
        $this->formFactory = $formFactory;
        $this->templating = $templating;
        $this->container = $container;
    }

    /**
     * @DI\Observe("create_form_directory")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->formFactory->create(new DirectoryType, new Directory());
        $response = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'directory'
            )
        );
        $event->setResponseContent($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_directory")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->formFactory->create(new DirectoryType(), new Directory());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $event->setResources(array($form->getData()));
            $event->stopPropagation();

            return;
        }

        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'directory'
            )
        );
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_directory")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $dir = $event->getResourceNode();
        $file = $this->resourceManager->download(array($dir));
        $response = new StreamedResponse();

        $response->setCallBack(
            function () use ($file) {
                readfile($file);
            }
        );

        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=archive');
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Connection', 'close');

        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("resource_directory_to_template")
     *
     * @param ExportDirectoryTemplateEvent $event
     */
    public function onExportTemplate(ExportDirectoryTemplateEvent $event)
    {
        $node = $event->getNode();
        //@todo one request to retrieve every directory and not needing a condition.
        $resource = $this->resourceManager->getResourceFromNode($node);
        $children = $resource instanceof Claroline\CoreBundle\Entity\Resource\Directory ?
            $this->resourceManager->getChildren($node, array('ROLE_ADMIN')): array();
        $dataChildren = array();

        foreach ($children as $child) {
            if ($child['type'] === 'directory') {
                $newEvent = $this->eventDispatcher->dispatch(
                    'resource_directory_to_template',
                    'ExportDirectoryTemplate',
                    array($this->resourceManager->getNode($child['id']))
                );
                $descr = $newEvent->getConfig();
                $dataChildren[] = $descr;
            }
        }

        $config = array('type' => 'directory', 'name' => $node->getName(), 'id' => $node->getId());
        $config['children'] = $dataChildren;
        $roles = $this->roleManager->getRolesByWorkspace($node->getWorkspace());

        foreach ($roles as $role) {
            $perms = $this->rightsManager->getMaximumRights(array($role->getName()), $node);
            $perms['canCreate'] = ($resource instanceof Claroline\CoreBundle\Entity\Resource\Directory) ?
                $this->rightsManager->getCreationRights(array($role->getName()), $node): array();

            $config['perms'][$this->roleManager->getRoleBaseName($role->getName())] = $perms;
        }

        $event->setConfig($config);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("resource_directory_from_template")
     *
     * @param ImportResourceTemplateEvent $event
     */
    public function onImportTemplate(ImportResourceTemplateEvent $event)
    {
        $config = $event->getConfig();
        $manager = $this->container->get('claroline.manager.resource_manager');
        $directory = new Directory();
        $directory->setName($config['name']);
        $directory = $manager->create(
            $directory,
            $this->resourceManager->getResourceTypeByName($config['type']),
            $event->getUser(),
            $event->getWorkspace(),
            $event->getParent(),
            null,
            $this->rightsManager->addRolesToPermsArray($event->getRoles(), $config['perms'])
        );
        $createdResources[$config['id']] = $directory->getResourceNode();

        foreach ($config['children'] as $child) {
            $newEvent = $this->eventDispatcher->dispatch(
                'resource_directory_from_template',
                'ImportResourceTemplate',
                array(
                    $child,
                    $directory->getResourceNode(),
                    $event->getUser(),
                    $event->getWorkspace(),
                    $event->getRoles(),
                    $createdResources
                )
            );
            $childResources = $newEvent->getCreatedResources();

            foreach ($childResources as $key => $value) {
                $createdResources[$key] = $value;
            }
        }

        $event->setCreatedResources($createdResources);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_directory")
     *
     * @param DeleteResourceEvent $event
     *
     * Removes a directory.
     */
    public function delete(DeleteResourceEvent $event)
    {
        $resource = $event->getResource();

        if ($resource->getParent() === null) {
            throw new \LogicException('Root directory cannot be removed');
        }

        $children = $this->resourceManager->getAllChildren($resource, false);

        foreach ($children as $child) {
            $this->eventDispatcher->dispatch(
                'delete_{$child->getResourceType()->getName()}',
                'DeleteResource',
                array($child)
            );
        }
    }

    /**
     * @DI\Observe("copy_directory")
     *
     * @param CopyResourceEvent $event
     *
     * Copy a directory.
     */
    public function copy(CopyResourceEvent $event)
    {
        $resourceCopy = new Directory();
        $event->setCopy($resourceCopy);
    }
}
