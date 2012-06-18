<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;
use Claroline\CoreBundle\Form\DirectoryType;
use Claroline\CoreBundle\Form\SelectResourceType;

/**
 * DirectoryManager will redirect to this controller once a directory is "open".
 */
class DirectoryController extends Controller
{
    /**
     * Returns the resource form.
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->get('form.factory')->create(new DirectoryType(), new Directory());
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
        $form = $this->get('form.factory')->create(new DirectoryType(), new Directory());
        $content = $this->render(
            $twigFile, array('form' => $form->createView(), 'parentId' => $id, 'type' => $type)
        );

        return $content;
    }

    /**
     * Creates a directory. Right/user/parent are set by the resource controller
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
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($directory);
        $em->flush();

        return $directory;
    }

    //todo: refactor this. See below.
    /**
     * Copies a directory.
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
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($directory);
        $em->flush();

        return $directory;
    }

    /**
     * Removes a directory and its children. If the instance number of an instance is 0,
     * the resource will be removed aswell.
     *
     * @param resourceInstance $resourceInstance
     */
    public function delete(ResourceInstance $resourceInstance)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $children = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->children($resourceInstance, true);

        foreach ($children as $child) {
            $rsrc = $child->getResource();
            $rsrc->removeResourceInstance($child);
            $em->remove($child);

            if ($rsrc->getInstanceCount() === 0) {
                $type = $child->getResourceType();

                if ($child->getResourceType()->getType() === 'directory') {
                    $em->remove($rsrc);
                } else {
                    $srv = $this->findResService($type);
                    $this->get($srv)->delete($rscr);
                }
            }
        }

        $rsrc = $resourceInstance->getResource();
        $rsrc->removeResourceInstance($resourceInstance);
        $em->remove($resourceInstance);

        if ($rsrc->getInstanceCount() === 0) {
            $type = $resourceInstance->getResourceType();
            $em->remove($rsrc);
        }

        $em->flush();
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
        $formResource = $this->get('form.factory')->create(new SelectResourceType(), new ResourceType());
        $em = $this->getDoctrine()->getEntityManager();
        $resourceInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($id);
        $workspace = $resourceInstance->getWorkspace();
        $resourcesInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->children($resourceInstance, true);
        $resourcesType = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();
        $content = $this->render(
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
    public function indexAction($workspaceId, $resourceId)
    {

        $content = $this->render(
            'ClarolineCoreBundle:Directory:index.html.twig');
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
        $services = $this->container->getParameter('claroline.resource_controllers');
        $names = array_keys($services);
        $serviceName = null;

        foreach ($names as $name) {
            $type = $this->get($name)->getResourceType();

            if ($type == $resourceType->getType()) {
                $serviceName = $name;
            }
        }

        return $serviceName;
    }
}