<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nico
 * Date: 04/07/13
 * Time: 15:33
 * To change this template use File | Settings | File Templates.
 */

namespace Icap\WikiBundle\Controller;


use Claroline\CoreBundle\Entity\User;
use Icap\WikiBundle\Entity\Wiki;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Form\SectionType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WikiController extends Controller{

    /**
     * @Route(
     *      "/{wikiId}",
     *      requirements={"wikiId" = "\d+"},
     *      name="icap_wiki_view"
     * )
     * @ParamConverter("wiki", class="IcapWikiBundle:Wiki", options={"id" = "wikiId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function viewAction(Wiki $wiki, User $user)
    {
        $this->checkAccess("OPEN", $wiki);

        $em = $this->getDoctrine()->getManager();
        $chapterRepository = $em->getRepository('IcapLessonBundle:Chapter');
        $query = $this->getDoctrine()->getManager()
            ->createQueryBuilder()
            ->select('node')
            ->from('Icap\\WikiBundle\\Entity\\Section', 'node')
            ->orderBy('node.root, node.left', 'ASC')
            ->where('node.root = :rootId')
            ->setParameter('rootId', $lesson->getRoot()->getId())
            ->getQuery()
        ;
        $options = array('decorate' => false);
        $tree = $sectionRepository->buildTree($query->getArrayResult(), $options);

        return array(
            'wiki' => $wiki,
            'tree' => $tree,
            'user' => $user,
            'workspace' => $wiki->getResourceNode()->getWorkspace(),
            'pathArray' => $wiki->getPathArray()
        );
    }

    /**
     * Displays form for creating new section to wiki
     * @param $wikiId, $parentSectionId
     * @Route(
     *      "/{wikiId}/section/new/{parentSectionId}",
     *      requirements = {
     *          "wikiId" = "\d+", 
     *          "parentSectionId" = "\d+"
     *      },
     *      defaults = {"parentSectionId" = 0},
     *      name="icap_wiki_new_section"
     * )
     * @ParamConverter("wiki", class="IcapWikiBundle:Wiki", options={"id" = "wikiId"})
     * @Template()
     */
    public function newSectionAction(Request $request, $wiki, $parentSectionId)
    {
        $form = $this->createForm(new SectionType());
        if ($request->isXMLHttpRequest()) {
            return $this->render(
                'IcapWikiBundle:Wiki:newSectionModal.html.twig',
                array(
                    'wiki' => $wiki,
                    'parentSectionId' => $parentSectionId,
                    'workspace' => $wiki->getResourceNode()->getWorkspace(),
                    'form' => $form->createView()
                )
            );
        }

        return array(
            'wiki' => $wiki,
            'parentSectionId' => $parentSectionId,
            'workspace' => $wiki->getResourceNode()->getWorkspace(),
            'form' => $form->createView()
        );

    }

    /**
     * @Route(
     *      "/{wikiId}/section/add/{parentSectionId}",
     *      requirements = {
     *          "wikiId" = "\d+", 
     *          "parentSectionId" = "\d+"
     *      },
     *      defaults = {"parentSectionId" = -1},
     *      name="icap_wiki_add_section"
     * )
     * @ParamConverter("wiki", class="IcapWikiBundle:Wiki", options={"id" = "wikiId"})
     * @Template()
     */
    public function addSectionAction(Request $request, $wiki, $parentSectionId)
    {
        $em = $this->getDoctrine()->getManager();
        $this->checkAccess("EDIT", $wiki);

        $form = $this->createForm(new SectionType());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $name = $data['name'];
            $text = $data['text'];

            $section = new Section();
            $section->setName($name);
            $section->setText($text);
            $section->setWiki($wiki);

            if ($parentSectionId !== null && $parentSectionId > -1) {
                $parent = $this->getSection($parentSectionId);
                $section->setParent($parent);
            }

            $em->persist($section);
            $em->flush();

            return $this->redirect(
                $this->generateUrl(
                    'icap_wiki_view',
                    array(
                        'wikiId' => $wiki->getId()
                    )
                )
            );
        }

        return array(
            'wiki' => $wiki,
            'workspace' => $wiki->getResourceNode()->getWorkspace(),
            'form' => $form->createView()
        );
    }

    /*
     * Retrieve a wiki from database
     */
    private function getWiki($wikiId)
    {
        $em = $this->getDoctrine()->getManager();
        $wiki = $em
            ->getRepository('IcapWikiBundle:Wiki')
            ->findOneBy(array('id' => $wikiId ));
        if ($wiki === null) {
            throw new NotFoundHttpException();
        }

        return $wiki;
    }

    /*
     * Retrieve a section from database
     */
    private function getSection($wiki, $sectionId)
    {
        $em = $this->getDoctrine()->getManager();
        $section = $em
            ->getRepository('IcapWikiBundle:Section')
            ->findOneBy(array('id' => $sectionId, 'wiki' => $wiki ));
        if ($section === null) {
            throw new NotFoundHttpException();
        }

        return $section;
    }
}