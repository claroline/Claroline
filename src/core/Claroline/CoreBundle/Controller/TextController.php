<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Entity\Resource\Revision;
use Claroline\CoreBundle\Form\TextType;

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
        
        $em->flush();
        
        return new Response('edited');        
    }
    
    //must be refactored: test function
    public function historyAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $text = $em->getRepository('ClarolineCoreBundle:Resource\Text')->find($id);
        $revisions = $text->getRevisions();        
        $size = count($revisions);
        $size--;
        $i=0;
        
        $patterns = array();
        $patterns[0] = '/<br\/>/';
        $patterns[1] = '/<\/p>/';
        
        $replacements = array();
        $replacements[1] = '&tokenbr';
        $replacements[0] = '&tokenp';
        
        while ($i < $size)
        {
            $old = $revisions[$i]->getContent();
            $i++;
            $new = $revisions[$i]->getContent();
            //echo($old);
            //echo($new);
            //preg replace
            $sold = preg_replace($patterns, $replacements, $old);
            //echo ($sold);
            $snew = preg_replace($patterns, $replacements, $new);
            //echo($snew);
            
            $diff = $this->get('claroline.text.manager')->PHPDiff($snew, $sold);
            echo($diff);
            echo'<br/>///////////////////////////////////////////////////////////////////<br/>';
        }
        
        return $this->render('ClarolineCoreBundle:Text:history.html.twig', array("revisions" => $revisions));
    }
}