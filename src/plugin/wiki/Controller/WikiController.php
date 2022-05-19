<?php

namespace Icap\WikiBundle\Controller;

use Claroline\AppBundle\Manager\PdfManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Manager\SectionManager;
use Icap\WikiBundle\Manager\WikiManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

/**
 * @Route("/wiki/{id}")
 * @EXT\ParamConverter("wiki", class="Icap\WikiBundle\Entity\Wiki", options={"mapping": {"id": "uuid"}})
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
    /** @var PdfManager */
    private $pdfManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        Environment $templating,
        WikiManager $wikiManager,
        SectionManager $sectionManager,
        PdfManager $pdfManager
    ) {
        $this->authorization = $authorization;
        $this->templating = $templating;
        $this->wikiManager = $wikiManager;
        $this->sectionManager = $sectionManager;
        $this->pdfManager = $pdfManager;
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
     */
    public function exportPdfAction(Wiki $wiki, User $user = null): StreamedResponse
    {
        $resourceNode = $wiki->getResourceNode();
        $this->checkPermission('EXPORT', $resourceNode, [], true);

        $isAdmin = $this->checkPermission('EDIT', $resourceNode);
        $sectionTree = $this->sectionManager->getSerializedSectionTree($wiki, $user, $isAdmin);

        return new StreamedResponse(function () use ($wiki, $sectionTree, $isAdmin, $user) {
            echo $this->pdfManager->fromHtml(
                $this->templating->render('@IcapWiki/wiki/pdf.html.twig', [
                    '_resource' => $wiki,
                    'tree' => $sectionTree,
                    'isAdmin' => $isAdmin,
                    'user' => $user,
                ])
            );
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($resourceNode->getName()).'.pdf',
        ]);
    }
}
