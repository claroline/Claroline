<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 3/20/15
 */

namespace Icap\InwicastBundle\Controller;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Controller extends BaseController
{
    protected function isUserGranted($action, $data)
    {
        return $this->getSecurityContext()->isGranted($action, $data);
    }

    protected function checkUserGranted($action, $data)
    {
        if (!$this->isUserGranted($action, $data)) {
            throw new AccessDeniedException();
        }
    }

    protected function checkAdmin()
    {
        if ($this->getSecurityContext()->isGranted('ROLE_ADMIN')) {
            return true;
        }

        throw new AccessDeniedException();
    }

    protected function getSecurityContext()
    {
        return $this->get('security.context');
    }

    /**
     * @return \Icap\InwicastBundle\Manager\MediaCenterManager
     */
    protected function getMediacenterManager()
    {
        return $this->get('inwicast.plugin.manager.mediacenter');
    }

    /**
     * @return \Icap\InwicastBundle\Manager\MediaManager
     */
    protected function getMediaManager()
    {
        return $this->get('inwicast.plugin.manager.media');
    }

    /**
     * @return \Icap\InwicastBundle\Manager\MediaCenterUserManager
     */
    protected function getMediacenterUserManager()
    {
        return $this->get('inwicast.plugin.manager.mediacenteruser');
    }

    protected function serializeObject($object)
    {
        $serializer = SerializerBuilder::create()->build();
        $serializationContext = new SerializationContext();
        $serializationContext->setSerializeNull(true);
        $objectJson = $serializer->serialize($object, 'json', $serializationContext);

        return json_decode($objectJson);
    }
}
