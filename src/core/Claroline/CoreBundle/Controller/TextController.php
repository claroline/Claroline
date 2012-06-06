<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Form\TextType;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Entity\Resource\Revision;

class TextController extends Controller
{
    public function editAction($id)
    {
        $request = $this->get('request');
        $user = $this->get('security.context')->getToken()->getUser();
        $text = $request->request->get('content');
        $em = $this->getDoctrine()->getEntityManager();
        $old = $em->getRepository('ClarolineCoreBundle:Resource\Text')->find($id);
        $version = $old->getVersion();        
        $revision = new Revision();
        $revision->setContent($text);
        $revision->setText($old);
        $revision->setVersion(++$version);  
        $revision->setUser($user);
        $em->persist($revision);
        $old->setVersion($version);
        $old->setLastRevision($revision);
        
        //edit the current resourceInstance
        $em->flush();
        
        return new Response('edited');        
    }
}