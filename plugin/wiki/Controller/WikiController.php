<?php

namespace Icap\WikiBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Manager\SectionManager;
use Icap\WikiBundle\Manager\WikiManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

/**
 * @Route("/wiki/{id}")
 * @EXT\ParamConverter("wiki", class="IcapWikiBundle:Wiki", options={"mapping": {"id": "uuid"}})
 */
class WikiController
{
    use PermissionCheckerTrait;

    /** @var Environment */
    private $templating;

    /** @var WikiManager */
    private $wikiManager;

    /** @var SectionManager */
    private $sectionManager;

    /**
     * WikiController constructor.
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        Environment $templating,
        WikiManager $wikiManager,
        SectionManager $sectionManager
    ) {
        $this->authorization = $authorization;
        $this->templating = $templating;
        $this->wikiManager = $wikiManager;
        $this->sectionManager = $sectionManager;
    }

    /**
     * @Route("/", name="apiv2_wiki_update", methods={"PUT"})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @return JsonResponse
     */
    public function updateAction(Wiki $wiki, Request $request)
    {
        $this->checkPermission('EDIT', $wiki->getResourceNode(), [], true);

        try {
            $this->wikiManager->updateWiki($wiki, json_decode($request->getContent(), true));

            return new JsonResponse($this->wikiManager->serializeWiki($wiki));
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * @Route("/pdf", name="apiv2_wiki_export_pdf")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @return JsonResponse
     */
    public function exportPdfAction(Wiki $wiki, User $user = null)
    {
        $resourceNode = $wiki->getResourceNode();
        $this->checkPermission('EXPORT', $resourceNode, [], true);

        $isAdmin = $this->checkPermission('EDIT', $resourceNode);
        $sectionTree = $this->sectionManager->getSerializedSectionTree($wiki, $user, $isAdmin);

        return new JsonResponse([
            'content' => $this->templating->render('IcapWikiBundle:wiki:pdf.html.twig', [
                '_resource' => $wiki,
                'tree' => $sectionTree,
                'isAdmin' => $isAdmin,
                'user' => $user,
            ]),
            'name' => $resourceNode->getName(),
        ]);
    }
}
