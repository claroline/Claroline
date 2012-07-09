<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
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
    public function getFormPage($renderType, $instanceParentId, $type)
    {
        $form = $this->get('form.factory')->create(new DirectoryType, new Directory());

        switch($renderType)
        {
            case 'widget': $twigFile = 'ClarolineCoreBundle:Resource:generic_form.html.twig'; break;
            case 'fullpage' : $twigFile = 'ClarolineCoreBundle:Resource:form_page.html.twig'; break;
        }

        return $this->render(
            $twigFile, array('form' => $form->createView(), 'parentId' => $instanceParentId, 'type' => $type)
        );
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
    public function add(Directory $directory, $id, User $user)
    {
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
                    $this->get($srv)->delete($rsrc);
                }
            }
        }

        $rsrc = $resourceInstance->getResource();

        $rsrc->removeResourceInstance($resourceInstance);
        $em->remove($resourceInstance);

        if ($rsrc->getInstanceCount() === 0) {
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
     * {@inheritdoc}
     *
     * @return array
     */
    public function getRoutedActions()
    {
        $repo = $this->getDoctrine()->getEntityManager()->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $resourceTypes = $repo->findBy(array('isListable' => '1'));
        $news = array();

        foreach ($resourceTypes as $resourceType) {
            $url = $this->get('router')->generate('claro_resource_creation_form', array('resourceType' => $resourceType->getType()));
            $news[$resourceType->getType()] = array('widget', $url);
        }

        $array = array(
            'new' => array('menu', $news),
        );


        return $array;
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