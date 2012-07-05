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
use Claroline\CoreBundle\Form\ResourceOptionsType;
use Claroline\CoreBundle\Library\Security\SymfonySecurity;

/**
 * This controller will manage resources.
 * It'll delegate create/update/delete/click actions for the different resource types
 * to the specified resource "manager" service.
 * It can add/remove a resource to a workspace.
 *
 * TODO:
 * javascript, add by copy => the response should be the new resource id
 * remove php navigation and replace it by javascript ~ almost done
 * REFACTOR RESOURCE MANANGER
 * - copy/paste
 * - sharable stuff
 * - popups instead of the stuff above
 * - tinyMCE for text
 * REFACTOR RESOURCE PICKER
 * when the server is slow, many ajax request can be sent... and everything get messy.
 * what about the rights in general ?
 * instance suppression: do we supress the original aswell ? can the creator remove every instance ?
 * Redirections & Responses
 * improved tests for move/add to workspace
 * text diff
 * linker
 * edit by copy
 * not using jquery from twitter bootstrap ? currently there is not enough room for 2 data trees in the js index and it should be fixed
 * create a resource manager in ordrer to remove some logic from the ResourceController.
 */
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
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $personnalWs = $user->getPersonnalWorkspace();
        //required for translations
        $resourcesType = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findAll();

        return $this->render(
                'ClarolineCoreBundle:Resource:index.html.twig', array('resourcesType' => $resourcesType, 'workspace' => $personnalWs)
        );
    }

    /**
     * Returns the resource options form.
     * name & share_type can be edited for every resource as long the user has the rights to do
     *
     * @param integer $instanceId
     */
    public function creationOptionsFormAction($instanceId)
    {
        $res = $this->getDoctrine()
            ->getEntityManager()
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->find($instanceId)
            ->getResource();

        $form = $this->get('form.factory')->create(new ResourceOptionsType(), $res);

        $request = $this->get('request');

        if ($request->isXmlHttpRequest()) {
            return $this->render('ClarolineCoreBundle:Resource:options_form.html.twig', array('instanceId' => $instanceId, 'form' => $form->createView()));
        }

        return $this->render('ClarolineCoreBundle:Resource:options_form_page.html.twig', array('instanceId' => $instanceId, 'form' => $form->createView()));
    }

    /**
     * Edits the resource options
     *
     * @param integer $resourceId
     */
    public function editOptionsAction($instanceId)
    {
        $request = $this->get('request');
        $em = $this->getDoctrine()->getEntityManager();
        $res = $em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance')->find($instanceId)->getResource();
        $form = $this->createForm(new ResourceOptionsType(), $res);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $res = $form->getData();
            $em->persist($res);
            $em->flush();

            if ($request->isXmlHttpRequest()) {
                $ri = $em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance')->find($instanceId);
                $ri->setResource($res);
                $content = $this->renderView("ClarolineCoreBundle:Resource:dynatree_resource.json.twig", array('resources' => array($ri)));
                $response = new Response($content);
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }

            return "edited";
        } else {
            if ($request->isXmlHttpRequest()) {
                return $this->render('ClarolineCoreBundle:Resource:options_form.html.twig', array('instanceId' => $instanceId, 'form' => $form->createView()));
            }
            return $this->render('ClarolineCoreBundle:Resource:options_form_page.html.twig', array('instanceId' => $instanceId, 'form' => $form->createView()));
        }
    }

    /**
     * Renders the specific resource form with it's claroline layout
     *
     * @param integer $instanceParentId
     * @param string  $type
     *
     * @return Response
     */
    public function getFormAction($renderType, $instanceParentId, $type)
    {
        $resourceType = $this->getDoctrine()->getEntityManager()->getRepository("Claroline\CoreBundle\Entity\Resource\ResourceType")->findOneBy(array('type' => $type));
        $name = $this->findResService($resourceType);
        $rsrcServ = $this->get($name);
        $content = $rsrcServ->getFormPage($renderType, $instanceParentId, $type);

        return new Response($content);
    }

    /**
     * Adds a resource. This method will delegate the resource creation to
     * the correct ResourceType service.
     *
     * if the workspaceId is 'null', the workspace will be the current user personnal Ws.
     * if it was requested through ajax, it'll respond with a json object containing the created resource datas
     * otherwise it'll redirect to the resource index.
     *
     * @param string  $type
     * @param integer $instanceParentId
     *
     * @return Response|RedirectResponse
     */
    public function createAction($type, $instanceParentId)
    {
        $request = $this->get('request');
        $resourceType = $this->getDoctrine()->getEntityManager()->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneBy(array('type' => $type));
        $name = $this->findResService($resourceType);
        $form = $this->get($name)->getForm();
        $form->bindRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $ri = $this->get('claroline.resource.creator')->create($data, $instanceParentId, $data, true);

            if (null !== $ri) {
                $content = $this->renderView("ClarolineCoreBundle:Resource:dynatree_resource.json.twig", array('resources' => array($ri)));
                $response = new Response($content);
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        }

        $content = $this->renderView(
            'ClarolineCoreBundle:Resource:generic_form.html.twig', array('form' => $form->createView(), 'parentId' => $instanceParentId, 'type' => $type)
        );
        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/html');

        return $response;
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
        $i = 0;

        foreach ($resourcesInstance as $resourceInstance) {
            if (!$this->get('security.context')->isGranted('VIEW', $resourceInstance)) {
                unset($resourcesInstance[$i]);
            }
            $i++;
        }

        $content = $this->renderView("ClarolineCoreBundle:Resource:dynatree_resource.{$format}.twig", array('resources' => $resourcesInstance));
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

        $content = $this->renderView("ClarolineCoreBundle:Resource:dynatree_resource.{$format}.twig", array('resources' => $roots));
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Moves an resource instance (changes his parent)
     * When it's moved to the root with javascript, the workspaceId isn't know so it must be passed as a parameter
     * otherwise child and parent workspace are equals.
     *
     * @param integer $idChild
     * @param integer $idParent
     * @param integer $workspaceDestinationId
     *
     * @return Response
     */
    public function moveResourceAction($idChild, $idParent)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $parent = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($idParent);
        $child = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($idChild);
        $this->get('claroline.resource.creator')->move($child, $parent);

        return new Response('success');
    }

    /**
     * Returns an action array as key(action name) => value(route) for
     * a resource type
     *
     * @param string $type
     */
    public function getJsonMenuAction($type)
    {
        if('all' != $type)
        {
            $resourceType = $this->getDoctrine()
                ->getEntityManager()
                ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
                ->findOneBy(array('type' => $type));

            $name = $this->findResService($resourceType);
            $actions = $this->get($name)->getRoutedActions();
            $actions = $this->addMandatoryActionsToMenu($actions);
            $json = $this->convertArrayToJsonMenu($actions);

            return new Response("{{$json}}");
        }

        $resourceTypes = $this->getDoctrine()->getEntityManager()->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();
        $response = "{";

        foreach($resourceTypes as $resourceType){
            $name = $this->findResService($resourceType);
            if($this->has($name)){
                $service = $this->get($name);
                if(method_exists($service, 'getRoutedActions')){
                    $actions = $service->getRoutedActions();
                    $actions = $this->addMandatoryActionsToMenu($actions);
                    $json = $this->convertArrayToJsonMenu($actions);
                    if('{'!= $response){
                        $response.= ",";
                    }
                    $response.= '"'.$service->getResourceType().'":'."{{$json}}";
                }
            }
        }

        $response .= "}";
        return new Response ($response);
    }

    /**
     * Adds a resource instance to a workspace.
     * Options must be must be 'ref' or 'copy'.
     * If $instanceId = 0, every resource is added
     * If $instanceDestinationId = 0, everything is added to the root
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
     * Removes a resource instance from a workspace
     *
     * @param integer $resourceId
     *
     * @return Response
     *
     * @throws AccessDeniedException
     */

    public function deleteInstanceAction($instanceId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $resourceInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceId);

        if (false === $this->get('security.context')->isGranted('DELETE', $resourceInstance)) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }

        if (null == $resourceInstance->getParent()){
            throw new \Exception("the workspace root can't be removed");
        }

        $resourceType = $resourceInstance->getResourceType();
        $name = $this->findResService($resourceType);

        if ($resourceInstance->getResourceType()->getType() === 'directory') {
            $this->get($name)->delete($resourceInstance);
        } else {
            $resourceInstance->getResource()->removeResourceInstance($resourceInstance);
            $em->remove($resourceInstance);

            if (0 === $resourceInstance->getResource()->getInstanceCount()) {
                $this->get($name)->delete($resourceInstance->getResource());
            }
        }

        $em->flush();

        return new Response('success');
    }

    /**
     * Returns the resource types defined in the platform (currently only json)
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
     * Currently not in use.
     * Get the list of instance for an instance.
     *
     * @param integer $instanceId
     *
     * @return Response
     */
    public function getResourcesReferenceAction($instanceId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $resourceInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceId);
        $resourcesInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->findBy(array('abstractResource' => $resourceInstance->getId()));
        $content = $this->renderView('ClarolineCoreBundle:Resource:resource_instance.json.twig', array('resourcesInstance' => $resourcesInstance));
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Returns the license list
     * Currently not in use
     *
     * @param string $format
     *
     * @return Response
     */
    public function getJsonLicensesListAction($format)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $licenses = $em->getRepository('Claroline\CoreBundle\Entity\License')->findAll();

        return $this->render("ClarolineCoreBundle:Resource:license_list.{$format}.twig", array('licenses' => $licenses));
    }

    /**
     * Returns the player list as an array.
     * Test function
     *
     * @param integer $id
     *
     * @return Response
     */
    public function getJsonPlayerListAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $resourceInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($id);
        $mime = $resourceInstance->getResource()->getMimeType();
        $services = $this->container->getParameter('claroline.resource_players');
        $names = array_keys($services);
        $i = 1;
        $arrayPlayer[0][0] = 'claroline.file.manager';
        $arrayPlayer[0][1] = $this->get('claroline.file.manager')->getPlayerName();

        foreach ($names as $name) {
            $srvMime = $this->get($name)->getMimeType();

            if ($mime->getName() == $srvMime || $mime->getType() == $srvMime) {
                $arrayPlayer[$i][0] = $name;
                $arrayPlayer[$i][1] = $this->get($name)->getPlayerName();
                $i++;
            }
        }

        return new Response(var_dump($arrayPlayer));
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
        $name = $this->findResService($resourceInstance->getResourceType());
        $resourceCopy = $this->get($name)->copy($resourceInstance->getResource(), $user);
        $resourceCopy->setResourceType($resourceInstance->getResourceType());
        $resourceCopy->addResourceInstance($ric);
        $ric->setResource($resourceCopy);

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
        $workspace = $parentInstance->getWorkspace();
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
        $workspace = $parentInstance->getWorkspace();
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
     * Returns the service's name for the ResourceType $resourceType
     *
     * @param ResourceType $resourceType
     *
     * @return string
     */
    private function findResService(ResourceType $resourceType)
    {
        $services = $this->container->getParameter('claroline.resource_controllers');
        $names = array_keys($services);

        foreach ($names as $name) {
            $type = $this->get($name)->getResourceType();

            if ($type == $resourceType->getType()) {
                return $name;
            }
        }
    }

    /**
     * Adds a permission to a resource instance
     *
     * @param integer $instanceId
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

        if($this->get('security.context')->isGranted(('OWNER'), $resourceInstance))
        {
            $this->container->get('claroline.resource.creator')->addInstanceRight($instanceId, $userId, intval($maskId));
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

        if($this->get('security.context')->isGranted(('OWNER'), $resourceInstance))
        {
            $this->container->get('claroline.resource.creator')->removeInstanceRight($instanceId, $userId, intval($maskId));
        } else {
            throw new AccessDeniedHttpException();
        }

        return new Response('success');
    }

    private function convertArrayToJsonMenu($array)
    {
        $json = '"items": {';
        $i = 0;
        foreach($array as $key => $item)
        {
            if ($item[0]!='menu'){
                $json.='"'.$key.'" : {"name": "'.$key.'", "return_type":"'.$item[0].'", "route":"'.$item[1].'"}';
            } else {
                $json .= '"'.$key.'" :{"name": "'.$key.'", ';
                $json .= $this->convertArrayToJsonMenu($item[1]);
                $json .= '}';
            }
            $i++;
            if($i < count($array)){$json.=',';}
        }
        $json.= "}";

         return $json;
    }

    public function addMandatoryActionsToMenu($array)
    {
        $router = $this->get('router');
        $array["delete"] = array("delete", $router->generate('claro_resource_remove_workspace', array('instanceId' => '%%instanceId%%')));
        $array["properties"] = array("widget", $router->generate('claro_resource_options_form', array('instanceId' => '%%instanceId%%')));

        return $array;
    }
}
