<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Form\DirectoryType;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Event\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\Event\ExportResourceTemplateEvent;
use Claroline\CoreBundle\Event\Event\ImportResourceTemplateEvent;

/**
 * @DI\Service
 */
class DirectoryListener implements ContainerAwareInterface
{
    private $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->ed = $container->get('claroline.event.event_dispatcher');
    }

    /**
     * @DI\Observe("create_form_directory")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new DirectoryType, new Directory());
        $response = $this->container->get('templating')->render(
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
        $form = $this->container
            ->get('form.factory')
            ->create(new DirectoryType(), new Directory());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $event->setResources(array($form->getData()));
            $event->stopPropagation();

            return;
        }

        $content = $this->container->get('templating')->render(
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
        $dir = $event->getResource();
        $file = $this->container->get('claroline.resource.exporter')->exportResources(array($dir->getId()));
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
     * @param ExportResourceTemplateEvent $event
     */
    public function onExportTemplate(ExportResourceTemplateEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $resourceRepo = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $resource = $event->getResource();
        //@todo one request to retrieve every directory and not needing a condition.
        $children = $resourceRepo->findChildren($resource, array('ROLE_ADMIN'));
        $dataChildren = array();

        foreach ($children as $child) {
            if ($child['type'] === 'directory') {
                $newEvent = new ExportResourceTemplateEvent($resourceRepo->find($child['id']));
                $this->ed->dispatch(
                    "resource_directory_to_template",
                    "ExportResourceTemplate",
                    array($resourceRepo->find($child['id']))
                );
                $descr = $newEvent->getConfig();
                $dataChildren[] = $descr;
            }
        }

        $config = array('type' => 'directory', 'name' => $resource->getName(), 'id' => $resource->getId());
        $config['children'] = $dataChildren;
        $roles = $em->getRepository('ClarolineCoreBundle:Role')->findByWorkspace($resource->getWorkspace());

        foreach ($roles as $role) {
            $perms = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
                ->findMaximumRights(array($role->getName()), $resource);
            $perms['canCreate'] = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
                ->findCreationRights(array($role->getName()), $resource);

            $config['perms'][rtrim(str_replace(range(0, 9), '', $role->getName()), '_')] = $perms;
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
        $manager = $this->container->get('claroline.resource.manager');
        $directory = new Directory();
        $directory->setName($config['name']);
        $manager->create(
            $directory,
            $event->getParent()->getId(),
            $config['type'],
            $event->getUser(),
            $config['perms']
        );
        $ed = $this->container->get('claroline.event.event_dispatcher');
        $createdResources[$config['id']] = $directory->getId();

        foreach ($config['children'] as $child) {
            $newEvent = new ImportResourceTemplateEvent(
                $child,
                $directory,
                $event->getUser()
            );
            $newEvent->setCreatedResources($createdResources);
            $ed->dispatch("resource_directory_from_template", "ImportResourceTemplate", array($createdResources));

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

        $children = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->getChildren($resource, false, 'path', 'DESC');

        foreach ($children as $child) {
            $this->ed->dispatch("delete_{$child->getResourceType()->getName()}", 'DeleteResource', $child);
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
        $dirType = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->findOneByName('directory');
        $resourceCopy->setResourceType($dirType);
        $event->setCopy($resourceCopy);
    }
}