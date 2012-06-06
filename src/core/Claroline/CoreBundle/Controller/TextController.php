<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Form\TextType;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\Resource\Text;

class TextController extends Controller
{
    public function editAction($id)
    {
        $request = $this->get('request');
        $user = $this->get('security.context')->getToken()->getUser();
        $content = $request->request->get('content');
        $em = $this->getDoctrine()->getEntityManager();
        $old = $em->getRepository('ClarolineCoreBundle:Resource\Text')->find($id);
        $version = $old->getVersion();
        $text = new Text();
        //$text->setUser($user);
        $text->setText($content);
        $text->setVersion(++$version);
        $text->setName($old->getName());
        $text->setSharable($old->isSharable());
        $text->setLicense($old->getLicense());
        $text->setParent($old);
        $text->setResourceType($old->getResourceType());
        $em->persist($text);
        
        //edit the current resourceInstance
        $em->flush();
        
        return new Response('edited');        
    }
}