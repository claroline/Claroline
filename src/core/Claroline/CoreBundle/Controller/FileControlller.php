<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class FileControlller extends Controller 
{
	/**
     * @Route(
     *     "resource/file",
     *     name="claro_file_hashtag"
     * )
     * @Method("POST")
     *
     * @param integer $id
     *
     * @return Response
     */
    public function hashtagAction()
    {
        $request = $this->get('request');
        if ($request->getMethod() === 'POST') {
            $postData = $request->request->all();
            $em = $this->get('doctrine.orm.entity_manager');
            $file = $em->getRepository('ClarolineCoreBundle:Resource\File')->find($postData['id']);
            $ds = DIRECTORY_SEPARATOR;

           return new Response(
               json_encode($file->getHashName()),
               200,
               array('Content-Type' => 'application/json')
           );
        }
	}
}
?>
