<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\Resource\Revision;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * TextManager will redirect to this controller once a directory is "open" or "edit".
 * This is more or less a test because it's hard to keep the diff between 2 html files and doesn't really
 * work properly for now. It's also untested.
 */
class TextController extends Controller
{

    /**
     * See https://github.com/paulgb/simplediff/blob/5bfe1d2a8f967c7901ace50f04ac2d9308ed3169/simplediff.php
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
            $this->diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
            array_slice($new, $nmax, $maxlen),
            $this->diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen))
        );
    }

    /**
     * Wraps the diff between 2 texts with HTML.
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
     * Test method.
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
     * Test method.
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

    /**
     * @Route(
     *     "/history/{text}",
     *     name="claro_text_history"
     * )
     *
     * @Template()
     *
     * Shows the diff between every text version. This function is a test.
     *
     * @param integer $textId
     *
     * @return type
     */
    public function historyAction(Text $text)
    {
        $collection = new ResourceCollection(array($text->getResourceNode()));
        $this->checkAccess('OPEN', $collection);

        $revisions = $text->getRevisions();
        $size = count($revisions);
        $size--;
        $d = $i = 0;
        $differences = null;

        while ($i < $size) {
            $new = $revisions[$i]->getContent();
            $i++;
            $old = $revisions[$i]->getContent();

            $old = $this->tokenize($old);
            $new = $this->tokenize($new);

            $diff = $this->htmlDiff($old, $new);
            $differences[$d] = $this->untokenize($diff);

            $d++;
        }

        return array(
            'differences' => $differences,
            'original' => $revisions[$size]->getContent(),
            '_resource' => $text
        );
    }

    /**
     * @Route(
     *     "/form/edit/{text}",
     *     name="claro_text_edit_form"
     * )
     *
     * @Template()
     *
     * Displays the text edition form.
     *
     * @param integer $textId
     *
     * @return Response
     */
    public function editFormAction(Text $text)
    {
        $collection = new ResourceCollection(array($text->getResourceNode()));
        $this->checkAccess('OPEN', $collection);

        $em = $this->container->get('doctrine.orm.entity_manager');
        $revisionRepo = $em->getRepository('ClarolineCoreBundle:Resource\Revision');

        return array(
            'text' => $revisionRepo->getLastRevision($text)->getContent(),
            '_resource' => $text
        );
    }

    /**
     * @Route(
     *     "/edit/{old}",
     *     name="claro_text_edit"
     * )
     *
     * Handles the text edition form submission.
     *
     * @param integer $textId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Text $old)
    {
        $collection = new ResourceCollection(array($old->getResourceNode()));
        $this->checkAccess('OPEN', $collection);

        $request = $this->get('request');
        $user = $this->get('security.context')->getToken()->getUser();
        $text = $request->request->get('content');
        $em = $this->getDoctrine()->getManager();
        $version = $old->getVersion();
        $revision = new Revision();
        $revision->setContent($text);
        $revision->setText($old);
        $revision->setVersion(++$version);
        $revision->setUser($user);
        $em->persist($revision);
        $old->setVersion($version);
        $em->flush();

        $route = $this->get('router')->generate(
            'claro_resource_open',
            array('resourceType' => 'text', 'node' => $old->getResourceNode()->getId())
        );

        return new RedirectResponse($route);
    }

    /**
     * @Route(
     *     "/open/{text}",
     *     name="claro_text_open"
     * )
     *
     * Handles the text edition form submission.
     *
     * @param integer $textId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function OpenAction(Text $text)
    {
        $revisionRepo = $this->getDoctrine()->getManager()
            ->getRepository('ClarolineCoreBundle:Resource\Revision');

        return $this->render(
            'ClarolineCoreBundle:Text:index.html.twig',
            array(
                'text' => $revisionRepo->getLastRevision($text)->getContent(),
                '_resource' => $text
            )
        );
    }

    /**
     * Checks if the current user has the right to perform an action on a ResourceCollection.
     * Be careful, ResourceCollection may need some aditionnal parameters.
     *
     * - for CREATE: $collection->setAttributes(array('type' => $resourceType))
     *  where $resourceType is the name of the resource type.
     * - for MOVE / COPY $collection->setAttributes(array('parent' => $parent))
     *  where $parent is the new parent entity.
     *
     * @param string             $permission
     * @param ResourceCollection $collection
     *
     * @throws AccessDeniedException
     */
    public function checkAccess($permission, ResourceCollection $collection)
    {
        if (!$this->get('security.context')->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}
