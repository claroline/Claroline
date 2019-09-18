<?php

namespace Claroline\DropZoneBundle\Controller\Resource;

use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @EXT\Route("/", options={"expose"=true})
 */
class DropzoneController extends Controller
{
    /**
     * For backwards compatibility with notification twigs.
     *
     * @EXT\Route("details/{dropzoneId}", name="claro_dropzone_detail_dropzone")
     * @EXT\ParamConverter("dropzone", class="ClarolineDropZoneBundle:Dropzone", options={"id" = "dropzoneId"})
     */
    public function openDropZoneAction(DropZone $dropzone)
    {
        $node = $dropzone->getResourceNode();

        return $this->redirect(
            $this->generateUrl('claro_index').
            '#/desktop/workspaces/open/'.$node->getWorkspace()->getSlug().'/resources/'.$node->getSlug()
        );
    }

    /**
     * For backwards compatibility with notification twigs.
     *
     * @EXT\Route("details/{dropzoneId}/{dropId}", name="claro_dropzone_detail_drop")
     * @EXT\ParamConverter("dropzone", class="ClarolineDropZoneBundle:Dropzone", options={"id" = "dropzoneId"})
     * @EXT\ParamConverter("drop", class="ClarolineDropZoneBundle:Drop", options={"id" = "dropId"})
     */
    public function openDropAction(DropZone $dropzone, Drop $drop)
    {
        $node = $dropzone->getResourceNode();

        return $this->redirect(
            $this->generateUrl('claro_index').
            '#/desktop/workspaces/open/'.$node->getWorkspace()->getSlug().'/resources/'.$node->getSlug().
            '/drop/'.$drop->getUuid()
        );
    }
}
