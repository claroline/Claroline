<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nico
 * Date: 04/07/13
 * Time: 15:33
 * To change this template use File | Settings | File Templates.
 */

namespace ICAP\WikiBundle\Controller;

use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use ICAP\WikiBundle\Entity\Section;
use ICAP\WikiBundle\Form\SectionType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WikiController extends Controller{

    public function indexAction()
    {
        return new Response("Index wiki");
    }

    private function getWiki($resourceId)
    {
        $em = $this->getDoctrine()->getManager();
        $wiki = $em
            ->getRepository('ICAPWikiBundle:Wiki')
            ->findOneBy(array('id' => $resourceId ));
        if (!$wiki) {
            throw $this->createNotFoundException('The wiki does not exist');
        }
        return $wiki;
    }

    /**
     * @Route(
     *      "/{resourceId}",
     *      requirements={"resourceId" = "\d+"},
     *      name="icap_wiki_edition"
     * )
     * @Template()
     */
    public function editionAction($resourceId)
    {
        $wiki = $this->getWiki($resourceId);

        return array(
            'wiki' => $wiki,
            'workspace' => $wiki->getResourceNode()->getWorkspace(),
            'pathArray' => $wiki->getPathArray()
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/create_section_form",
     *      requirements={"resourceId" = "\d+"},
     *      name="icap_wiki_create_section_form"
     * )
     * @Template()
     */
    public function createSectionFormAction(Request $request, $resourceId)
    {
        $wiki = $this->getWiki($resourceId);
        $form = $this->createForm(new SectionType());
        if ($request->isXMLHttpRequest()) {
            return $this->render(
                'ICAPWikiBundle:Wiki:createSectionModal.html.twig',
                array(
                    'wiki' => $wiki,
                    'workspace' => $wiki->getResourceNode()->getWorkspace(),
                    'form' => $form->createView()
                )
            );
        }

        return array(
            'wiki' => $wiki,
            'workspace' => $wiki->getResourceNode()->getWorkspace(),
            'form' => $form->createView()
        );

    }

    /**
     * @Route(
     *      "/{resourceId}/create_section",
     *      requirements={"resourceId" = "\d+"},
     *      name="icap_wiki_create_section"
     * )
     * @Template()
     */
    public function createSectionAction(Request $request, $resourceId)
    {
        $em = $this->getDoctrine()->getManager();
        $wiki = $this->getWiki($resourceId);
        $this->isAllowToEdit($wiki);

        $form = $this->createForm(new SectionType());
        $form->bind($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $name = $data['name'];
            $text = $data['text'];

            $section = new Section();
            $section->setName($name);
            $section->setText($text);
            $section->setWiki($wiki);

            $em->persist($section);
            $em->flush();

            return $this->redirect(
                $this->generateUrl(
                    'icap_wiki_edition',
                    array(
                        'resourceId' => $wiki->getId()
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

    protected function isAllowToEdit($wiki)
    {
        $this->isAllow($wiki, 'EDIT');
    }

    protected function isAllow($wiki, $actionName)
    {
        $collection = new ResourceCollection(array($wiki));
        if (false === $this->get('security.context')->isGranted($actionName, $collection)) {
            throw new AccessDeniedException();
        }
    }

}