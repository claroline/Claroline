<?php

namespace Claroline\ImagePlayerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;

class ImagePlayerController extends Controller
{
    /**
     * @Route(
     *     "/image/{node}",
     *     name="claro_image"
     * )
     */
    public function fileAction(ResourceNode $node)
    {
        $image = $this->get('claroline.manager.resource_manager')->getResourceFromNode($node);

        $path = $this->container->getParameter(
            'claroline.param.files_directory'
        ).DIRECTORY_SEPARATOR.$image->getHashName();

        return new Response(file_get_contents($path));
    }
}
