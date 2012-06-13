<?php

namespace Claroline\CoreBundle\Library\Manager;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Security\RightManager\RightManagerInterface;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;
use Claroline\CoreBundle\Form\DirectoryType;
use Claroline\CoreBundle\Form\SelectResourceType;

/**
 * this service is called when the resource controller is using a "directory" resource
 */
class DirectoryManager
{

    /** @var Doctrine\ORM\EntityManager */
    protected $em;

    /** @var RightManagerInterface */
    protected $rightManager;

    /** @var FormFactory */
    protected $formFactory;

    /** @var ContainerInterface */
    protected $container;

    /** @var TwigEngine */
    protected $templating;

    /**
     * Constructor
     *
     * @param FormFactory $formFactory
     * @param EntityManager $em
     * @param RightManagerInterface $rightManager
     * @param ContainerInterface $container
     * @param ResourceManager $resourceManager
     * @param TwigEngine $templating
     */
    public function __construct(FormFactory $formFactory, EntityManager $em, RightManagerInterface $rightManager, ContainerInterface $container, TwigEngine $templating)
    {
        $this->em = $em;
        $this->rightManager = $rightManager;
        $this->formFactory = $formFactory;
        $this->container = $container;
        $this->templating = $templating;
    }

    /**
     * Returns the resource form.
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->formFactory->create(new DirectoryType(), new Directory());
    }

    /**
     * Returns the form in a template. $twigFile will contain the default template called
     * but you can do your own.
     *
     * @param string  $twigFile
     * @param integer $id
     * @param string  $type
     *
     * @return string
     */
    public function getFormPage($twigFile, $id, $type)
    {
        $form = $this->formFactory->create(new DirectoryType(), new Directory());
        $content = $this->templating->render(
            $twigFile, array('form' => $form->createView(), 'parentId' => $id, 'type' => $type)
        );

        return $content;
    }

    /**
     * Create a directory. Right/user/parent are set by the resource controller
     * but you can use them here aswell.
     *
     * @param Form    $form
     * @param integer $id   the parent id
     * @param User    $user the user creating the directory
     *
     * @return Directory
     */
    public function add($form, $id, User $user)
    {
        $directory = new Directory();
        $name = $form['name']->getData();
        $directory->setName($name);
        $this->em->persist($directory);
        $this->em->flush();

        return $directory;
    }

    //todo: refactor this. See below.
    /**
     * Copy a directory.
     * /!\ not totally done yet. Copy a directory by ref or by copy isn't the same and
     * children must be copied aswell.
     *
     * This method shouldn't be called because the resource controller already manager
     * children when a directory is copied in a workspace.
     * /!\ refactor this.
     *
     * @param AbstractResource $resource
     * @param User $user
     *
     * @return \Claroline\CoreBundle\Entity\Resource\Directory
     */
    public function copy(AbstractResource $resource, User $user)
    {
        $directory = new Directory();
        $directory->setName($resource->getName());
        $this->em->persist($directory);
        $this->em->flush();

        return $directory;
    }

    /**
     * Remove a directory and its children. If the instance number of an instance is 0,
     * the resource will be removed aswell.
     *
     * @param resourceInstance $resourceInstance
     */
    public function delete(ResourceInstance $resourceInstance)
    {
        $children = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->children($resourceInstance, true); {
            foreach ($children as $child) {
                if ($child->getResourceType()->getType() != 'directory') {
                    $rsrc = $child->getResource();
                    $this->em->remove($child);
                    $rsrc->decrInstance();

                    if ($rsrc->getInstanceAmount() == 0) {
                        $type = $child->getResourceType();
                        $srv = $this->findResService($type);
                        $this->container->get($srv)->delete($child->getResource());
                    }
                } else {
                    $rsrc = $child->getResource();
                    $this->em->remove($child);
                    $rsrc->decrInstance();

                    if ($rsrc->getInstanceAmount() == 0) {
                        $type = $child->getResourceType();
                        $this->em->remove($rsrc);
                    }
                }
            }
        }

        $rsrc = $resourceInstance->getResource();
        $this->em->remove($resourceInstance);
        $rsrc->decrInstance();

        if ($rsrc->getInstanceAmount() == 0) {
            $type = $resourceInstance->getResourceType();
            $this->em->remove($rsrc);
        }
        $this->em->flush();
    }

    /**
     * Returns the resource type as a string, it'll be used by the resource controller to find this service
     *
     * @return string
     */
    public function getResourceType()
    {
        return "directory";
    }

    /**
     * Default action for a directory. It's what happens when you left click on it. This one is a particular because
     * it uses the resource:index.html.twig file with the current directory as a root.
     *
     * @param integer $id
     *
     * @return Response
     */
    public function getDefaultAction($id)
    {
        $formResource = $this->formFactory->create(new SelectResourceType(), new ResourceType());
        $resourceInstance = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($id);
        $workspace = $resourceInstance->getWorkspace();
        $resourcesInstance = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->children($resourceInstance, true);
        $resourcesType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();
        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:index.html.twig', array('form_resource' => $formResource->createView(), 'resourceInstances' => $resourcesInstance, 'parentId' => $id, 'resourcesType' => $resourcesType, 'directory' => $resourceInstance, 'workspace' => $workspace));
        $response = new Response($content);

        return $response;
    }

    /**
     * Fired when OpenAction is fired for a directory in the resource controller.
     * It's send with the workspaceId to keep the context.
     *
     * @param integer $workspaceId
     * @param integer $resourceInstance
     *
     * @return Response
     */
    public function indexAction($workspaceId, $resourceInstance)
    {
        $content = $this->templating->render(
            'ClarolineCoreBundle:Directory:index.html.twig', array('directory' => $resourceInstance));
        $response = new Response($content);

        return $response;
    }

    /**
     * Returns the service's name for the ResourceType $resourceType
     *
     * @param ResourceType $resourceType
     *
     * @return string
     */
    private function findResService($resourceType)
    {
        $services = $this->container->getParameter("resource.service.list");
        $names = array_keys($services);
        $serviceName = null;

        foreach ($names as $name) {
            $type = $this->container->get($name)->getResourceType();

            if ($type == $resourceType->getType()) {
                $serviceName = $name;
            }
        }

        return $serviceName;
    }
}