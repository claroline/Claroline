<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\Resource\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class FileControlller extends Controller
{
    /**
     * @Route(
     *     "resource/img/{node}",
     *     name="claro_file_get_image",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * @param integer $id
     *
     * @return Response
     */
    public function getImg(ResourceNode $node)
    {
        $file = $this->get('claroline.manager.resource.manager')->getResourceFromNode($node);
        $imgpath = $this->container->getParameter('claroline.param.files_directory') . DIRECTORY_SEPARATOR
            . $file->getHashName();

        $response = new StreamedResponse();
        $response->setCallBack(
            function () use ($imgpath) {
                readfile($imgpath);
            }
        );

        $response->headers->set('Content-Type', $file->getMimeType());

        return $response;
    }
}
