<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\MimeType;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\WorkspaceRole;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\DirectoryType;
use Claroline\CoreBundle\Form\SelectResourceType;
use Claroline\CoreBundle\Form\ResourcePropertiesType;
use Claroline\CoreBundle\Library\Security\SymfonySecurity;

use Claroline\CoreBundle\Library\Resource\CreateResourceEvent;
use Claroline\CoreBundle\Library\Resource\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Library\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Library\Resource\CustomActionResourceEvent;

class ResourceController extends Controller
{
    /**
     * Renders the root resources for the personnal workspace of the current
     * logged user.
     *
     * @return Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        //required for translations
        $resourcesType = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findAll();

        return $this->render(
                'ClarolineCoreBundle:Resource:index.html.twig', array('resourcesType' => $resourcesType)
        );
    }

    /**
     * Renders the creation form for a given resource type.
     *
     * @param string  $resourceType
     *
     * @return Response
     */
    public function creationFormAction($resourceType)
    {
        $resourceType = strtolower(str_replace(' ', '_', $resourceType));
        $event = new CreateFormResourceEvent();
        $this->get('event_dispatcher')->dispatch("create_form_{$resourceType}", $event);

        return new Response($event->getResponseContent());
    }

    /**
     * Creates a resource.
     *
     * @param string  $resourceType
     * @param integer $parentInstanceId
     *
     * @return Response
     */
    public function createAction($resourceType, $parentInstanceId)
    {
        // dispatch a create_[resource_type_name] event, containing the request object;
        // if the event is filled with an entity, create an instance, put rights on it, and send success response,
        // otherwise send form with validation errors
        // note : it's up to the plugin resource listener to validate the data and depending
        // on the result, to fill the event wit'renderType' => 'widget'h a success response or with the initial form
        // with validation errors.
        $resourceType = strtolower(str_replace(' ', '_', $resourceType));
        $event = new CreateResourceEvent();
        $this->get('event_dispatcher')->dispatch("create_{$resourceType}", $event);
        $response = new Response();

        if (($resource = $event->getResource()) instanceof AbstractResource) {
            $manager = $this->get('claroline.resource.manager');
            $instance = $manager->create($resource, $parentInstanceId);
            $content = $this->renderView(
                'ClarolineCoreBundle:Resource:resources.json.twig',
                array('resources' => array($instance))
            );
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($content);
        } else {
            $response->setContent($event->getErrorFormContent());
        }

        return $response;
    }


    /**
     * Removes a resource instance from a workspace
     *
     * @param integer $resourceId
     *
     * @return Response
     */
    public function deleteAction($instanceId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $resourceInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceId);

        if (1 === $resourceInstance->getResource()->getInstanceCount()) {
            if ($resourceInstance->getResourceType()->getType() !== 'directory') {
                $event = new DeleteResourceEvent(array($resourceInstance->getResource()));
                $this->get('event_dispatcher')->dispatch("delete_{$resourceInstance->getResourceType()->getType()}", $event);
            } else {
                $this->deleteDirectory($resourceInstance);
            }
        }

        $resourceInstance->getResource()->removeResourceInstance($resourceInstance);
        $em->remove($resourceInstance);
        $em->flush();

        return new Response('success');
    }

    private function deleteDirectory($resourceInstance)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $children = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->children($resourceInstance, true);

        foreach ($children as $child) {
            $rsrc = $child->getResource();

            if ($rsrc->getInstanceCount() === 1) {

                if ($child->getResourceType()->getType() === 'directory') {
                    $em->remove($rsrc);
                } else {
                    $event = new DeleteResourceEvent(array($child->getResource()));
                    $this->get('event_dispatcher')->dispatch("delete_{$child->getResourceType()->getType()}", $event);
                }
            }

            $rsrc->removeResourceInstance($child);
            $em->remove($child);
        }

        $em->remove($resourceInstance->getResource());
        $em->flush();
    }

    public function customAction($resourceType, $action, $instanceId)
    {
        $instance = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->find($instanceId);
        $event = new CustomActionResourceEvent($instance->getResource()->getId());
        $this->get('event_dispatcher')->dispatch("{$action}_{$resourceType}", $event);

        // TODO : if $event->getResponse() === null -> exception

        return $event->getResponse();
    }

    /**
     * Renders the form allowing to edit the base properties (currently only the name and the
     * sharable/public/private attribute) of a given resource.
     *
     * @param integer $resourceId
     *
     * @return Response
     */
    public function basePropertiesFormAction($resourceId)
    {
        $request = $this->get('request');
        $res = $this->getDoctrine()->getEntityManager()->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($resourceId);
        $form = $this->createForm(new ResourcePropertiesType(), $res);

        if($request->isXmlHttpRequest()){
            return $this->render('ClarolineCoreBundle:Resource:properties_form.html.twig', array('resourceId' => $resourceId, 'form' => $form->createView()));
        }

        return $this->render('ClarolineCoreBundle:Resource:properties_form_page.html.twig', array('resourceId' => $resourceId, 'form' => $form->createView()));
    }

    /**
     * Updates the base properties of a given resource.
     *
     * @param integer $resourceId
     *
     * @return Response
     */
    public function updateBasePropertiesAction($instanceId)
    {
        $request = $this->get('request');
        $em = $this->getDoctrine()->getEntityManager();
        $res = $em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance')->find($instanceId)->getResource();
        $form = $this->createForm(new ResourcePropertiesType(), $res);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $res = $form->getData();
            $em->persist($res);
            $em->flush();

            if ($request->isXmlHttpRequest()) {
                $ri = $em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance')->find($instanceId);
                $ri->setResource($res);
                $content = $this->renderView("ClarolineCoreBundle:Resource:resources.json.twig", array('resources' => array($ri)));
                $response = new Response($content);
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            if ($request->isXmlHttpRequest()) {
                return $this->render('ClarolineCoreBundle:Resource:properties_form.html.twig', array('instanceId' => $instanceId, 'form' => $form->createView()));
            }
            return $this->render('ClarolineCoreBundle:Resource:properties_form_page.html.twig', array('instanceId' => $instanceId, 'form' => $form->createView()));
        }
    }

    /**
     * Moves an resource instance (changes his parent)
     *
     * @param integer $idChild
     * @param integer $idParent
     *
     * @return Response
     */
    public function moveResourceAction($idChild, $idParent)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $parent = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($idParent);
        $child = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($idChild);
        $this->get('claroline.resource.manager')->move($child, $parent);

        return new Response('success');
    }

    /**
     * This method will redirect to the ResourceInstance ResourceType manager
     * editAction. Options must be 'ref' or 'copy'
     *
     * @param integer $instanceId
     * @param string  $options
     *
     * @return Response
     */
    public function editAction($instanceId, $options)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $resourceInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceId);

        if ($options == 'copy') {
            $workspace = $resourceInstance->getWorkspace();
            $user = $this->get('security.context')->getToken()->getUser();
            $name = $this->findResService($resourceInstance->getResourceType());
            $copy = $this->get($name)->copy($resourceInstance->getResource(), $user);

            $instanceCopy = new ResourceInstance();
            $instanceCopy->setParent($resourceInstance->getParent());
            $instanceCopy->setResource($copy);
            $instanceCopy->setCopy(false);
            $instanceCopy->setWorkspace($resourceInstance->getWorkspace());
            $instanceCopy->setCreator($user);

            $copy->setResourceType($resourceInstance->getResourceType());
            $copy->addResourceInstance($instanceCopy);

            $em->persist($copy);
            $em->persist($instanceCopy);
            $em->remove($resourceInstance);
            $em->flush();

            $roleCollaborator = $workspace->getCollaboratorRole();
            $rightManager = $this->get('claroline.security.right_manager');
            $rightManager->addRight($instanceCopy, $roleCollaborator, MaskBuilder::MASK_VIEW);

            return new Response('copied');
        } else {
            $name = $this->findResService($resourceInstance->getResourceType());
            $response = $this->get($name)->editFormPageAction($resourceInstance->getResource()->getId());

            return new Response($response);
        }
    }

    /**
     * Returns a ResourceInstance node for dynatree.
     *
     * @param integer $instanceId
     * @param string  $format
     *
     * @return Response
     */
    public function getNodeAction($instanceId, $format)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $parent = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceId);
        $resourcesInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->getListableChildren($parent);
        /*$i = 0;

        foreach ($resourcesInstance as $resourceInstance) {
            if (!$this->get('security.context')->isGranted('VIEW', $resourceInstance)) {
                unset($resourcesInstance[$i]);
            }
            $i++;
        }*/

        $content = $this->renderView("ClarolineCoreBundle:Resource:resources.{$format}.twig", array('resources' => $resourcesInstance));
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Gets every workspace root nodes for a user
     *
     * @param integer $userId
     * @param string  $format
     *
     * @return Response
     */
    public function getRootNodeAction($userId, $format)
    {
        $em = $this->getDoctrine()->getEntityManager();

        if ('null' != $userId) {
            $user = $em->find('Claroline\CoreBundle\Entity\User', $userId);
        } else {
            $user = $this->get('security.context')->getToken()->getUser();
        }

        $workspaces = $em->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')->getAllWsOfUser($user);
        $roots = array();

        foreach ($workspaces as $workspace) {
            $root = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->findOneBy(array('parent' => null, 'workspace' => $workspace->getId()));
            $roots[] = $root;
        }

        $content = $this->renderView("ClarolineCoreBundle:Resource:resources.{$format}.twig", array('resources' => $roots));
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Returns an action array as key(action name) => value(route) for
     * a resource type
     *
     * @param string $type
     */
    public function getJsonMenuAction($type)
    {
        $resourceTypes = $this->getDoctrine()
            ->getEntityManager()
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findAll();

        return $this->render('ClarolineCoreBundle:Resource:resource_menu.json.twig', array('resourceTypes' => $resourceTypes));
    }

    /**
     * Adds a resource instance to a workspace.
     * Options must be must be 'ref' or 'copy'.
     *
     * @param integer $resourceId
     * @param string  $options
     * @param integer $instanceDestinationId
     *
     * @return Response
     */
    public function addToWorkspaceAction($instanceId, $options, $instanceDestinationId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $resource = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceId)->getResource();
        $parent = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceDestinationId);

        if ($resource->getShareType() == AbstractResource::PUBLIC_RESOURCE) {
            if ($options == 'ref') {
                $this->copyFirstReferenceInstance($instanceId, $parent);
            } else {
                $this->copyFirstCopyInstance($instanceId, $parent);
            }

            return new Response('copied');
        }

        return new Response('success');
    }

    /**
     * Returns the resource types defined in the platform (currently only in json format)
     *
     * $listable can be "all", "false" and "true"
     *
     * @param string $format
     * @param string $listable
     *
     * @return Response
     */
    public function getResourceTypesAction($format, $listable)
    {
        $repo = $this->getDoctrine()->getEntityManager()->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        if ($listable == 'all') {
            $resourceTypes = $repo->findAll();
        } else if ($listable == 'true') {
            $resourceTypes = $repo->findBy(array('isListable' => '1'));
        } else if ($listable == 'false') {
            $resourceTypes = $repo->findBy(array('isListable' => '0'));
        }

        $content = $this->renderView("ClarolineCoreBundle:Resource:resource_type.{$format}.twig", array('resourceTypes' => $resourceTypes));
        $response = new Response($content);

        return $response;
    }

    /**
     * Renders the sf2 masks lists
     *
     * @param string $format
     * @param string $type   the type of list (all | res)
     *
     * @return Response
     */
    public function getMasksAction($type, $format)
    {
        switch ($type) {
            case 'res': $masks = SymfonySecurity::getResourcesMasks();
                break;
            default: $masks = SymfonySecurity::getSfMasks();
                break;
        }
        return $this->render("ClarolineCoreBundle:Resource:masks_list.{$format}.twig", array('masks' => $masks));
    }

    /**
     * Returns a copied resource instance. The resource itself is not copied.
     *
     * @param ResourceInstance $resourceInstance
     *
     * @return ResourceInstance
     */
    private function copyByReferenceResourceInstance(ResourceInstance $resourceInstance)
    {
        $ric = new ResourceInstance();
        $ric->setCreator($this->get('security.context')->getToken()->getUser());
        $ric->setCopy(true);
        $ric->setWorkspace($resourceInstance->getWorkspace());
        $ric->setResource($resourceInstance->getResource());
        $resourceInstance->getResource()->addResourceInstance($ric);

        return $ric;
    }

    /**
     * Returns a copied resource instance. The resource itself is also copied
     *
     * @param ResourceInstance $resourceInstance
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceInstance
     */
    private function copyByCopyResourceInstance(ResourceInstance $resourceInstance)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $ric = new ResourceInstance();
        $ric->setCreator($user);
        $ric->setCopy(false);
        $ric->setWorkspace($resourceInstance->getWorkspace());

        // dispatch a copy_[resource_type_name] event,
        // ensure the event is filled with an abstract resource instance


        //$name = $this->findResService($resourceInstance->getResourceType());

        $this->get('doctrine.orm.entity_manager')->flush();
        $em = $this->get('doctrine.orm.entity_manager');

        if($resourceInstance->getResourceType()->getType()=='directory') {
            $resourceCopy = new Directory();
            $resourceCopy->setName($resourceInstance->getResource()->getName());
            $resourceCopy->setCreator($user);
            $resourceCopy->setResourceType($em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneByType('directory'));
            $em->persist($resourceCopy);
        }
        else {
            $event = new CopyResourceEvent($resourceInstance->getResource());
            $resourceType = strtolower(str_replace(' ', '_', $resourceInstance->getResourceType()->getType()));
            $this->get('event_dispatcher')->dispatch("copy_{$resourceType}", $event);
            $resourceCopy = $event->getCopy();
            $resourceCopy->setResourceType($resourceInstance->getResourceType());
            $resourceCopy->addResourceInstance($ric);
        }

        $ric->setResource($resourceCopy);
        $this->get('doctrine.orm.entity_manager')->flush();

        return $ric;
    }

    /**
     * Set the children of a copied by reference resource instance.
     *
     * @param ResourceInstance $parentInstance
     * @param ResourceInstance $parentCopy
     */
    private function setChildrenByReferenceCopy(ResourceInstance $parentInstance, ResourceInstance $parentCopy)
    {
        $workspace = $parentCopy->getWorkspace();
        $em = $this->getDoctrine()->getEntityManager();
        $children = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->children($parentInstance, true);
        $rightManager = $this->get('claroline.security.right_manager');
        $roleCollaborator = $workspace->getCollaboratorRole();

        foreach ($children as $child) {
            if ($child->getResource()->getShareType() == AbstractResource::PUBLIC_RESOURCE) {
                $copy = $this->copyByReferenceResourceInstance($child);
                $copy->setParent($parentCopy);
                $copy->setWorkspace($workspace);
                $em->persist($copy);
                $this->setChildrenByReferenceCopy($child, $copy);
                $rightManager->addRight($copy, $roleCollaborator, MaskBuilder::MASK_VIEW);
            }
        }

        $em->flush();
    }

    /**
     * Set the children of a copied by copy resource instance
     *
     * @param ResourceInstance $parentInstance
     * @param ResourceInstance $parentCopy
     */
    private function setChildrenByCopyCopy(ResourceInstance $parentInstance, ResourceInstance $parentCopy)
    {
        $workspace = $parentCopy->getWorkspace();
        var_dump($workspace->getId());
        $em = $this->getDoctrine()->getEntityManager();
        $children = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->children($parentInstance, true);
        $rightManager = $this->get('claroline.security.right_manager');
        $roleCollaborator = $workspace->getCollaboratorRole();

        foreach ($children as $child) {
            if ($child->getResource()->getShareType() == AbstractResource::PUBLIC_RESOURCE) {
                $copy = $this->copyByCopyResourceInstance($child);
                $copy->setParent($parentCopy);
                $copy->setWorkspace($workspace);
                $em->persist($copy);
                $em->flush();
                $this->setChildrenByReferenceCopy($child, $copy);
                $rightManager->addRight($copy, $roleCollaborator, MaskBuilder::MASK_VIEW);
            }
        }
    }

    /**
     * Copy a resource instance by reference and put it in a workspace.
     *
     * @param integer           $instanceId
     * @param ResourceInstance  $parent
     */
    private function copyFirstReferenceInstance($instanceId, $parent)
    {
        $workspace = $parent->getWorkspace();
        $em = $this->getDoctrine()->getEntityManager();
        $roleCollaborator = $workspace->getCollaboratorRole();
        $resourceInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceId);
        $resourceInstanceCopy = $this->copyByReferenceResourceInstance($resourceInstance);
        $resourceInstanceCopy->setWorkspace($workspace);
        $resourceInstanceCopy->setParent($parent);
        $em->persist($resourceInstanceCopy);
        $resourceInstance->getResource()->addResourceInstance($resourceInstance);
        $em->flush();
        $user = $this->get('security.context')->getToken()->getUser();
        $rightManager = $this->get('claroline.security.right_manager');
        $rightManager->addRight($resourceInstanceCopy, $user, MaskBuilder::MASK_OWNER);
        $rightManager->addRight($resourceInstanceCopy, $roleCollaborator, MaskBuilder::MASK_VIEW);
        $this->setChildrenByReferenceCopy($resourceInstance, $resourceInstanceCopy);
    }

    /**
     * Copy a resource instance by copy and put it in a workspace.
     *
     * @param integer           $instanceId
     * @param ResourceInstance  $parent
     */
    private function copyFirstCopyInstance($instanceId, $parent)
    {
        $workspace = $parent->getWorkspace();
        $em = $this->getDoctrine()->getEntityManager();
        $roleCollaborator = $workspace->getCollaboratorRole();
        $resourceInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceId);
        $resourceInstanceCopy = $this->copyByCopyResourceInstance($resourceInstance);
        $resourceInstanceCopy->setWorkspace($workspace);
        $resourceInstanceCopy->setParent($parent);
        $em->persist($resourceInstanceCopy);
        $em->flush();
        $user = $this->get('security.context')->getToken()->getUser();
        $rightManager = $this->get('claroline.security.right_manager');
        $rightManager->addRight($resourceInstanceCopy, $roleCollaborator, MaskBuilder::MASK_VIEW);
        $rightManager->addRight($resourceInstanceCopy, $user, MaskBuilder::MASK_OWNER);
        $this->setChildrenByCopyCopy($resourceInstance, $resourceInstanceCopy);
    }

    /**
     * Adds a permission to a resource instance
     *
     * @param integer                alert('move !');$instanceId
     * @param integer $userId
     * @param integer $maskId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws AccessDeniedHttpException
     */
    public function addInstanceUserPermissionAction($instanceId, $userId, $maskId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $resourceInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceId);

        if ($this->get('security.context')->isGranted(('OWNER'), $resourceInstance)) {
            $this->container->get('claroline.resource.manager')->addInstanceRight($instanceId, $userId, intval($maskId));
        } else {
            throw new AccessDeniedHttpException();
        }

        return new Response('success');
    }

    /**
     * Removes a permission to a resource instance
     *
     * @param integer $instanceId
     * @param integer $userId
     * @param integer $maskId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws AccessDeniedHttpException
     */
    public function removeInstanceUserPermissionAction($instanceId, $userId, $maskId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $resourceInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceId);

        if ($this->get('security.context')->isGranted(('OWNER'), $resourceInstance)) {
            $this->container->get('claroline.resource.manager')->removeInstanceRight($instanceId, $userId, intval($maskId));
        } else {
            throw new AccessDeniedHttpException();
        }

        return new Response('success');
    }
}