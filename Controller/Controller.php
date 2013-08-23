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
     * @param Blog $blog
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
    /**
     * @param string $permission
     *
     * @param Blog $blog
     *
     * @return bool
     */
    protected function isUserGranted($permission, Blog $blog)
    {
        $checkPermission = false;
        if ($this->get('security.context')->isGranted($permission, new ResourceCollection(array($blog)))) {
            $checkPermission = true;
        }

        return $checkPermission;
    }

    /**
     * @param Blog $blog
     *
     * @return array
     */
    protected function getArchiveDatas(Blog $blog)
    {
        $postDatas          = $this->get('icap.blog.post_repository')->findArchiveDatasByBlog($blog);
        $archiveDatas = array();

        $translator = $this->get('translator');

        foreach ($postDatas as $postData) {
            $archiveDatas[$postData['year']][] = array(
                'year'  => $postData['year'],
                'month' => $translator->trans('month.' . date("F", mktime(0, 0, 0, $postData['month'], 10)), array(), 'platform'),
                'count' => $postData['number']
            );
        }

        return $archiveDatas;
    }
}
