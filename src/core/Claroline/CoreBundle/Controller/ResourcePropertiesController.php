<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Claroline\CoreBundle\Entity\Resource\IconType;
use Claroline\CoreBundle\Form\ResourcePropertiesType;
use Claroline\CoreBundle\Form\ResourceNameType;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Claroline\CoreBundle\Library\Event\LogResourceUpdateEvent;


class ResourcePropertiesController extends Controller
{
    /**
     * @Route(
     *     "/rename/form/{resourceId}",
     *     name="claro_resource_rename_form",
     *     options={"expose"=true}
     * )
     *
     * Displays the form allowing to rename a resource.
     *
     * @param integer $resourceId the resource id
     *
     * @return Response
     */
    public function renameFormAction($resourceId)
    {
        $resource = $this->getDoctrine()
            ->getEntityManager()
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->find($resourceId);
        $collection = new ResourceCollection(array($resource));
        $this->checkAccess('EDIT', $collection);
        $form = $this->createForm(new ResourceNameType(), $resource);

        return $this->render(
            'ClarolineCoreBundle:Resource:rename_form.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route(
     *     "/rename/{resourceId}",
     *     name="claro_resource_rename",
     *     options={"expose"=true}
     * )
     *
     * Renames a resource.
     *
     * @param integer $resourceId the resource id
     *
     * @return Response
     */
    public function renameAction($resourceId)
    {
        $request = $this->get('request');
        $em = $this->getDoctrine()->getManager();
        $resource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->find($resourceId);
        $collection = new ResourceCollection(array($resource));
        $this->checkAccess('EDIT', $collection);
        $form = $this->createForm(new ResourceNameType(), $resource);
        $form->bind($request);

        if ($form->isValid()) {
            $unitOfWork = $em->getUnitOfWork();
            $unitOfWork->computeChangeSets();
            $changeSet = $unitOfWork->getEntityChangeSet($resource);

            $em->persist($resource);
            $em->flush();
            $content = json_encode(array($resource->getName()));
            $response = new Response($content);
            $response->headers->set('Content-Type', 'application/json');

            $log = new LogResourceUpdateEvent($resource, $changeSet);
            $this->get('event_dispatcher')->dispatch('log', $log);

            return $response;
        }

        return $this->render(
            'ClarolineCoreBundle:Resource:rename_form.html.twig',
            array('resourceId' => $resourceId, 'form' => $form->createView())
        );
    }

    /**
     * @Route(
     *     "/properties/form/{resourceId}",
     *     name="claro_resource_form_properties",
     *     options={"expose"=true}
     * )
     *
     * Displays the resource properties form.
     *
     * @param integer $resourceId the resource id
     *
     * @return Response
     */
    public function propertiesFormAction($resourceId)
    {
        $resource = $this->getDoctrine()
            ->getEntityManager()
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->find($resourceId);

        $form = $this->createForm(new ResourcePropertiesType(), $resource);

        return $this->render(
            'ClarolineCoreBundle:Resource:form_properties.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route(
     *     "/properties/edit/{resourceId}",
     *     name="claro_resource_edit_properties",
     *     options={"expose"=true}
     * )
     *
     * Changes the resource properties.
     *
     * @param integer $resourceId the resource id
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function changePropertiesAction($resourceId)
    {
        $request = $this->get('request');
        $em = $this->get('doctrine.orm.entity_manager');
        $resource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->find($resourceId);

        if (!$this->get('security.context')->getToken()->getUser() === $resource->getCreator()) {
             throw new AccessDeniedException("You're not the owner of this resource");
        }

        $form = $this->createForm(new ResourcePropertiesType(), $resource);
        $form->bind($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $file = $data->getUserIcon();

            if ($file !== null) {
                $this->removeOldIcon($resource);
                $manager = $this->get('claroline.resource.icon_creator');
                $icon = $manager->createCustomIcon($file);
                $em->persist($icon);

                if (get_class($resource) === 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut') {
                    $icon = $icon->getShortcutIcon();
                }

                $resource->setIcon($icon);
            }

            $resource->setName($data->getName());

            $unitOfWork = $em->getUnitOfWork();
            $unitOfWork->computeChangeSets();
            $changeSet = $unitOfWork->getEntityChangeSet($resource);

            if (array_key_exists('icon', $changeSet)) {
                $icons = $changeSet['icon'];
                if ($icons[0] != null) {
                    $icons[0] = $icons[0]->getRelativeUrl();
                }
                if ($icons[1] != null) {
                    $icons[1] = $icons[1]->getRelativeUrl();
                }
                $changeSet['icon'] = $icons;
            }

            $em->persist($resource);
            $em->flush();
            $content = "{";

            if (isset($icon)) {
                $content .= '"icon": "' . $icon->getRelativeUrl() . '"';
            } else {
                $content .= '"icon": "' . $resource->getIcon()->getRelativeUrl() . '"';
            }

            $content .= ', "name": "' . $resource->getName() . '"';
            $content .= '}';
            $response = new Response($content);
            $response->headers->set('Content-Type', 'application/json');

            $log = new LogResourceUpdateEvent($resource, $changeSet);
            $this->get('event_dispatcher')->dispatch('log', $log);

            return $response;
        }

        return $this->render(
            'ClarolineCoreBundle:Resource:form_properties.html.twig',
            array('resourceId' => $resourceId, 'form' => $form->createView())
        );
    }

    /**
     * Removes the icon of a resource from the web/thumbnails folder.
     *
     * @param AbstractResource $resource the resource
     */
    private function removeOldIcon($resource)
    {
        $icon = $resource->getIcon();

        if ($icon->getIconType()->getType() == IconType::CUSTOM_ICON) {
            $pathName = $this->container->getParameter('claroline.param.thumbnails_directory')
                . DIRECTORY_SEPARATOR . $icon->getIconLocation();
            if (file_exists($pathName)) {
                unlink($pathName);
            }
        }
    }

    /**
     * Checks if the current user has the right to do an action on a ResourceCollection.
     * Be carrefull, ResourceCollection may need some aditionnal parameters.
     *
     * - for CREATE: $collection->setAttributes(array('type' => $resourceType))
     *  where $resourceType is the name of the resource type.
     * - for MOVE / COPY $collection->setAttributes(array('parent' => $parent))
     *  where $parent is the new parent entity.
     *
     *
     * @param string $permission
     * @param ResourceCollection $collection
     *
     * @throws AccessDeniedException
     */
    private function checkAccess($permission, $collection)
    {
        if (!$this->get('security.context')->isGranted($permission, $collection)) {
            throw new AccessDeniedException(print_r($collection->getErrorsForDisplay(), true));
        }
    }
}

