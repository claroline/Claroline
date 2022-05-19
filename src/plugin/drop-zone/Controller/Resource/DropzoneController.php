<?php

namespace Claroline\DropZoneBundle\Controller\Resource;

use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route("/", options={"expose"=true})
 */
class DropzoneController
{
    /** @var RouterInterface */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * For backwards compatibility with notification twigs.
     *
     * @Route("details/{dropzoneId}", name="claro_dropzone_detail_dropzone")
     * @EXT\ParamConverter("dropzone", class="Claroline\DropZoneBundle\Entity\Dropzone", options={"id" = "dropzoneId"})
     */
    public function openDropZoneAction(DropZone $dropzone): RedirectResponse
    {
        $node = $dropzone->getResourceNode();

        return new RedirectResponse(
            $this->router->generate('claro_index').
            '#/desktop/workspaces/open/'.$node->getWorkspace()->getSlug().'/resources/'.$node->getSlug()
        );
    }

    /**
     * For backwards compatibility with notification twigs.
     *
     * @Route("details/{dropzoneId}/{dropId}", name="claro_dropzone_detail_drop")
     * @EXT\ParamConverter("dropzone", class="Claroline\DropZoneBundle\Entity\Dropzone", options={"id" = "dropzoneId"})
     * @EXT\ParamConverter("drop", class="Claroline\DropZoneBundle\Entity\Drop", options={"id" = "dropId"})
     */
    public function openDropAction(DropZone $dropzone, Drop $drop): RedirectResponse
    {
        $node = $dropzone->getResourceNode();

        return new RedirectResponse(
            $this->router->generate('claro_index').
            '#/desktop/workspaces/open/'.$node->getWorkspace()->getSlug().'/resources/'.$node->getSlug().
            '/drop/'.$drop->getUuid()
        );
    }
}
