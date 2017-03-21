<?php

namespace Icap\WikiBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Icap\WikiBundle\Entity\Wiki;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WikiController extends Controller
{
    /**
     * @Route(
     *      "/{wikiId}.{_format}",
     *      defaults={"_format":"html"},
     *      requirements={"wikiId" = "\d+", "_format":"html|pdf"},
     *      name="icap_wiki_view",
     *      options = { "expose" = true }
     * )
     * @ParamConverter("wiki", class="IcapWikiBundle:Wiki", options={"id" = "wikiId"})
     */
    public function viewAction(Wiki $wiki, Request $request)
    {
        $this->checkAccess('OPEN', $wiki);
        $isAdmin = $this->isUserGranted('EDIT', $wiki);
        $user = $this->getLoggedUser();
        $sectionRepository = $this->get('icap.wiki.section_repository');
        $tree = $sectionRepository->buildSectionTree($wiki, $isAdmin, $user);
        $deletedSections = $sectionRepository->findDeletedSections($wiki);
        $format = $request->get('_format');
        $response = new Response();
        $this->render(sprintf('IcapWikiBundle:Wiki:view.%s.twig', $format), [
            '_resource' => $wiki,
            'tree' => $tree,
            'workspace' => $wiki->getResourceNode()->getWorkspace(),
            'isAdmin' => $isAdmin,
            'user' => $user,
            'deletedSections' => $deletedSections,
        ], $response);
        if ($format === 'pdf') {
            return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml(
                    $response->getContent(),
                    [
                        'outline' => true,
                        'footer-right' => '[page]/[toPage]',
                        'footer-spacing' => 3,
                        'footer-font-size' => 8,
                    ],
                    true
                ),
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="'.$wiki->getResourceNode()->getName(),
                ]
            );
        }

        return $response;
    }
}
