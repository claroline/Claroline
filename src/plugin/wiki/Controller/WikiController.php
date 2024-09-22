<?php

namespace Icap\WikiBundle\Controller;

use Exception;
use Claroline\AppBundle\Manager\PdfManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Manager\SectionManager;
use Icap\WikiBundle\Manager\WikiManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

#[Route(path: '/wiki/{id}')]
class WikiController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly Environment $templating,
        private readonly WikiManager $wikiManager,
        private readonly SectionManager $sectionManager,
        private readonly PdfManager $pdfManager
    ) {
        $this->authorization = $authorization;
    }

    #[Route(path: '/', name: 'apiv2_wiki_update', methods: ['PUT'])]
    public function updateAction(#[MapEntity(mapping: ['id' => 'uuid'])] Wiki $wiki, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $wiki->getResourceNode(), [], true);

        try {
            $this->wikiManager->updateWiki($wiki, json_decode($request->getContent(), true));

            return new JsonResponse($this->wikiManager->serializeWiki($wiki));
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    #[Route(path: '/pdf', name: 'apiv2_wiki_export_pdf')]
    public function exportPdfAction(#[MapEntity(mapping: ['id' => 'uuid'])] Wiki $wiki, User $user = null): StreamedResponse
    {
        $resourceNode = $wiki->getResourceNode();
        $this->checkPermission('EXPORT', $resourceNode, [], true);

        $isAdmin = $this->checkPermission('EDIT', $resourceNode);
        $sectionTree = $this->sectionManager->getSerializedSectionTree($wiki, $user, $isAdmin);

        return new StreamedResponse(function () use ($wiki, $sectionTree, $isAdmin, $user): void {
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
