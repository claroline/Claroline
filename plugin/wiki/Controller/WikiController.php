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
use Icap\WikiBundle\Form\WikiOptionsType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;

class WikiController extends Controller{

    /**
     * @Route(
     *      "/{wikiId}.{_format}",
     *      defaults={"_format":"html"},
     *      requirements={"wikiId" = "\d+", "_format":"html|pdf"},
     *      name="icap_wiki_view"
     * )
     * @ParamConverter("wiki", class="IcapWikiBundle:Wiki", options={"id" = "wikiId"})
     */
    public function viewAction(Wiki $wiki, Request $request)
    {
        $this->checkAccess("OPEN", $wiki);
        $isAdmin = $this->isUserGranted("EDIT", $wiki);
        $user = $this->getLoggedUser();
        $sectionRepository = $this->get('icap.wiki.section_repository');
        $tree = $sectionRepository->buildSectionTree($wiki, $isAdmin);
        $format = $request->get('_format');
        $response = new Response();
        $this->render(sprintf('IcapWikiBundle:Wiki:view.%s.twig', $format), array(
            '_resource' => $wiki,
            'tree' => $tree,
            'workspace' => $wiki->getResourceNode()->getWorkspace(),
            'isAdmin' => $isAdmin,
            'user' => $user
        ), $response);
        if ($format == "pdf") {
            return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml(
                    $response->getContent(),
                    array(
                        'outline' => true,
                        'footer-right' => '[page]/[toPage]',
                        'footer-spacing' => 3,
                        'footer-font-size' => 8
                    ),
                    true
                ),
                200,
                array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'inline; filename="'.$wiki->getResourceNode()->getName()
                )
            );

        }

        return $response;
    }

    /**
     * @Route(
     *      "/configure/{wikiId}/{page}",
     *      requirements={
     *          "wikiId" = "\d+",
     *          "page" = "\d+",
     *      },
     *      defaults = {
     *          "page" = 1
     *      },
     *      name="icap_wiki_configure"
     * )
     * @ParamConverter("wiki", class="IcapWikiBundle:Wiki", options={"id" = "wikiId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function configureAction(Request $request, Wiki $wiki, $user, $page)
    {
        $this->checkAccess("EDIT", $wiki);

        return $this->persistWikiOptions($request, $wiki, $user, $page);
    }

    private function persistWikiOptions (Request $request, Wiki $wiki, User $user, $page) {
        $form = $this->createForm(new WikiOptionsType(), $wiki);
        $sectionRepository = $this->get('icap.wiki.section_repository');
        $query = $sectionRepository->findDeletedSectionsQuery($wiki);
        $adapter = new DoctrineORMAdapter($query);
        $pager   = new PagerFanta($adapter);
        $pager->setMaxPerPage(20);
        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $exception) {
            throw new NotFoundHttpException();
        }
        if ("POST" === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $flashBag = $this->get('session')->getFlashBag();
                $translator = $this->get('translator');

                try{
                    $em = $this->getDoctrine()->getManager();
                    $unitOfWork = $em->getUnitOfWork();
                    $unitOfWork->computeChangeSets();
                    $changeSet = $unitOfWork->getEntityChangeSet($wiki);
                    $em->persist($wiki);
                    $em->flush();

                    $this->dispatchWikiConfigureEvent($wiki, $changeSet);

                    $flashBag->add('success', $translator->trans('icap_wiki_options_save_success', array(), 'icap_wiki'));
                } catch (\Exception $exception) {
                    $flashBag->add('error', $translator->trans('icap_wiki_options_save_error', array(), 'icap_wiki'));
                }

                return $this->redirect(
                    $this->generateUrl(
                        'icap_wiki_view',
                        array(
                            'wikiId' => $wiki->getId()
                        )
                    )
                );
            }
        }

        return array(
            '_resource' => $wiki,
            'workspace' => $wiki->getResourceNode()->getWorkspace(),
            'pager' => $pager,
            'form' => $form->createView()
        );
    }
}
