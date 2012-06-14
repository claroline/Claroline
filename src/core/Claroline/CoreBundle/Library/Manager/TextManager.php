<?php

namespace Claroline\CoreBundle\Library\Manager;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Security\RightManager\RightManagerInterface;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Entity\Resource\Revision;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\TextType;

/**
 * this service is called when the resource controller is using a "text" resource
 */
class TextManager //implements ResourceInterface
{

    /** @var EntityManager */
    protected $em;

    /** @var FormFactory */
    protected $formFactory;

    /** @var TwigEngine */
    protected $templating;

    /**
     * Constructor
     *
     * @param EntityManager $em
     * @param FormFactory $formFactory
     * @param TwigEngine $templating
     */
    public function __construct(EntityManager $em, FormFactory $formFactory, TwigEngine $templating)
    {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->templating = $templating;
    }

    /**
     * Returns the resource type as a string, it'll be used by the resource controller to find this service
     *
     * @return string
     */
    public function getResourceType()
    {
        return "text";
    }

    /**
     * Returns the resource form.
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->formFactory->create(new TextType);
    }

    /**
     * Returns the form in a template. $twigFile will contain the default template called
     * but it's not used here.
     *
     * @param string  $twigTemp
     * @param integer $id
     * @param string  $type
     *
     * @return string
     */
    public function getFormPage($twigTemp, $id, $type)
    {
        $form = $this->formFactory->create(new TextType);
        $content = $this->templating->render('ClarolineCoreBundle:Text:form_page.html.twig', array('form' => $form->createView(), 'id' => $id, 'type' => $type));

        return $content;
    }

    /**
     * Create a text. Right/user/parent are set by the resource controller
     * but you can use them here aswell.
     *
     * @param Form    $form
     * @param integer $id
     * @param User    $user
     *
     * @return Text
     */
    public function add($form, $id, User $user)
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

    /**
     * Default action for a text. It's what happens when you left click on it.
     *
     * @param integer $resourceId
     *
     * @return Response
     */
    public function getDefaultAction($resourceId)
    {
        $text = $this->em->getRepository('ClarolineCoreBundle:Resource\Text')->find($resourceId);
        $content = $this->templating->render('ClarolineCoreBundle:Text:index.html.twig', array('text' => $text->getLastRevision()->getContent(), 'textId' => $resourceId));

        return new Response($content);
    }

    /**
     * Returns a response (wich contains a form).
     * Edit action for a text.
     *
     * @param integer $resourceId
     *
     * @return Response
     */
    public function editAction($resourceId)
    {
        $text = $this->em->getRepository('ClarolineCoreBundle:Resource\Text')->find($resourceId);
        $content = $this->templating->render('ClarolineCoreBundle:Text:edit.html.twig', array('text' => $text->getLastRevision()->getContent(), 'textId' => $resourceId));

        return new Response($content);
    }

    /**
     * found on https://github.com/paulgb/simplediff/blob/5bfe1d2a8f967c7901ace50f04ac2d9308ed3169/simplediff.php
     * Paul's Simple Diff Algorithm v 0.1
     * (C) Paul Butler 2007 <http://www.paulbutler.org/>
     * May be used and distributed under the zlib/libpng license.
     * This code is intended for learning purposes; it was written with short
     * code taking priority over performance. It could be used in a practical
     * application, but there are a few ways it could be optimized.
     * Given two arrays, the function diff will return an array of the changes.
     * I won't describe the format of the array, but it will be obvious
     * if you use print_r() on the result of a diff on some test data.
     * htmlDiff is a wrapper for the diff command, it takes two strings and
     * returns the differences in HTML. The tags used are <ins> and <del>,
     * which can easily be styled with CSS.
     *
     * @param array $old
     * @param array $new
     *
     * @return array
     */
    public function diff($old, $new)
    {
        $maxlen = 0;

        foreach ($old as $oindex => $ovalue) {
            $nkeys = array_keys($new, $ovalue);
            foreach ($nkeys as $nindex) {
                $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
                        $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
                if ($matrix[$oindex][$nindex] > $maxlen) {
                    $maxlen = $matrix[$oindex][$nindex];
                    $omax = $oindex + 1 - $maxlen;
                    $nmax = $nindex + 1 - $maxlen;
                }
            }
        }
        if ($maxlen == 0) {

            return array(array('d' => $old, 'i' => $new));
        }

        return array_merge(
            $this->diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)), array_slice($new, $nmax, $maxlen), $this->diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
    }

    /**
     * wraps the diff between 2 texts with HTML.
     *
     * @param string $old
     * @param string $new
     *
     * @return string
     */
    public function htmlDiff($old, $new)
    {
        $diff = $this->diff(explode(' ', $old), explode(' ', $new));
        $ret = "";

        foreach ($diff as $k) {
            if (is_array($k)) {
                $ret .= (!empty($k['d']) ? "<b style='color:red'>" . implode(' ', $k['d']) . "</b> " : '') .
                        (!empty($k['i']) ? "<b style='color:green'>" . implode(' ', $k['i']) . "</b> " : '');
            } else {
                $ret .= $k . ' ';
            }
        }

        return $ret;
    }

    /**
     * Test method
     *
     * @param string $txt
     *
     * @return string
     */
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

    /**
     * Test method
     *
     * @param string $txt
     *
     * @return string
     */
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