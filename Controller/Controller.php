<?php

namespace ICAP\BlogBundle\Controller;

use ICAP\BlogBundle\Entity\Blog;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Controller extends BaseController
{
    /**
     * @param string $permission
     *
     * @param Blog   $blog
     *
     * @throws AccessDeniedException
     */
    protected function checkAccess($permission, Blog $blog)
    {
        $collection = new ResourceCollection(array($blog));

        if (!$this->get('security.context')->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}
