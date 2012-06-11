<?php
namespace Claroline\CoreBundle\Library\Manager;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Security\RightManager\RightManagerInterface;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Entity\Resource\Revision;
use Claroline\CoreBundle\Form\TextType;

class TextManager //implements ResourceInterface
{
    /** @var EntityManager */
    protected $em;
    /** @var FormFactory */
    protected $formFactory;
    /** @var TwigEngine */
    protected $templating;
    
    public function __construct(EntityManager $em, FormFactory $formFactory, TwigEngine $templating)
    {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->templating = $templating;
    }
    
    public function getResourceType()
    {
        return "text";
    }
    
    public function getForm()
    {
        return $this->formFactory->create(new TextType);
    }
    
    public function getFormPage($twigTemp, $id, $type)
    {
        $form = $this->formFactory->create(new TextType);
        $content = $this->templating->render('ClarolineCoreBundle:Text:form_page.html.twig', array('form' => $form->createView(), 'id' => $id, 'type' => $type));
        
        return $content;
    }  
    
    public function add($form, $id, $user)
    {
         $name = $form['name']->getData();
         $data = $form['text']->getData();
         $revision = new Revision();
         $revision->setContent($data);
         $revision->setUser($user);
         $this->em->persist($revision);
         $text = new Text();
         $text->setName($name);
         $text->setLastRevision($revision);
         $this->em->persist($text);
         $revision->setText($text);
         $this->em->flush();
         
         return $text;
    }
    
    public function getDefaultAction($id)
    {
        $text = $this->em->getRepository('ClarolineCoreBundle:Resource\Text')->find($id);
        $content = $this->templating->render('ClarolineCoreBundle:Text:index.html.twig', array('text' => $text->getLastRevision()->getContent(), 'id' => $id));
        
        return new Response($content);
    }
    
    public function editAction($id)
    {
        $text = $this->em->getRepository('ClarolineCoreBundle:Resource\Text')->find($id);
        $content = $this->templating->render('ClarolineCoreBundle:Text:edit.html.twig', array('text' => $text->getLastRevision()->getContent(), 'id' => $id));
        
        return new Response($content);
    }  
    
    /*
     * found on https://github.com/paulgb/simplediff/blob/5bfe1d2a8f967c7901ace50f04ac2d9308ed3169/simplediff.php
     *
     
    Paul's Simple Diff Algorithm v 0.1
    (C) Paul Butler 2007 <http://www.paulbutler.org/>
    May be used and distributed under the zlib/libpng license.
    This code is intended for learning purposes; it was written with short
    code taking priority over performance. It could be used in a practical
    application, but there are a few ways it could be optimized.
    Given two arrays, the function diff will return an array of the changes.
    I won't describe the format of the array, but it will be obvious
    if you use print_r() on the result of a diff on some test data.
    htmlDiff is a wrapper for the diff command, it takes two strings and
    returns the differences in HTML. The tags used are <ins> and <del>,
    which can easily be styled with CSS.
    */
    public function diff($old, $new)
    {
        $maxlen = 0;
        
        foreach ($old as $oindex => $ovalue)
        {
            $nkeys = array_keys($new, $ovalue);
            foreach ($nkeys as $nindex)
            {
                $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
                        $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
                if ($matrix[$oindex][$nindex] > $maxlen)
                {
                    $maxlen = $matrix[$oindex][$nindex];
                    $omax = $oindex + 1 - $maxlen;
                    $nmax = $nindex + 1 - $maxlen;
                }
            }
        }
        if ($maxlen == 0)
            return array(array('d' => $old, 'i' => $new));
        return array_merge(
                        $this->diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)), array_slice($new, $nmax, $maxlen), $this->diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
    }
    
    //see diff
    function htmlDiff($old, $new)
    {
        $diff = $this->diff(explode(' ', $old), explode(' ', $new));
        $ret = "";
        
        foreach ($diff as $k)
        {
            if (is_array($k))
                $ret .= (!empty($k['d']) ? "<b style='color:red'>" . implode(' ', $k['d']) . "</b> " : '') .
                        (!empty($k['i']) ? "<b style='color:green'>" . implode(' ', $k['i']) . "</b> " : '');
            else
                $ret .= $k . ' ';
        }
        
        return $ret;
    }
    
    public function tokenize($txt)
    {
        $patterns = array();
        $patterns[0] = '/<br \/>/';
        $patterns[1] = '/<p>/';
        $patterns[2] = '/<\/p>/';
        //$patterns[3] = '/\./';
        
        $replacements = array();
        $replacements[0] = ' &tokenbr ';
        $replacements[1] = ' &tokenbp ';
        $replacements[2] = ' &tokenep ';
        //$replacements[3] = ' &. ';
        
        $txt = preg_replace($patterns, $replacements, $txt);
        
        return $txt;
    }
    
    public function untokenize($txt)
    {  
        $patterns = array();
        $patterns[0] = '/&tokenbr/';
        $patterns[1] = '/&tokenbp/';
        $patterns[2] = '/&tokenep/';
        //$patterns[3] = '/ &. /';
        
        $replacements = array();
        $replacements[0] = '<br />';
        $replacements[1] = '<p>';
        $replacements[2] = '</p>';
        //$replacements[3] = '.';
        
        $txt = preg_replace($patterns, $replacements, $txt);
        
        return $txt;
    }
}