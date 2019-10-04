<?php

namespace Icap\WikiBundle\Controller\Resource;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Manager\SectionManager;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * @EXT\Route("/wiki", options={"expose"=true})
 */
class WikiController
{
    use PermissionCheckerTrait;

    /** @var SectionManager */
    private $sectionManager;

    /** @var EngineInterface */
    private $templating;

    /** @var Pdf */
    private $pdfRenderer;

    /**
     * SectionController constructor.
     *
     * @param SectionManager  $sectionManager
     * @param EngineInterface $templating
     * @param $pdfRenderer
     */
    public function __construct(
        SectionManager $sectionManager,
        EngineInterface $templating,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->sectionManager = $sectionManager;
        $this->templating = $templating;
        $this->authorization = $authorization;
    }

    /**
     * @EXT\Route(
     *     "/{id}/pdf",
     *     name="icap_wiki_export_pdf",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "wiki",
     *     class="IcapWikiBundle:Wiki",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param Wiki      $wiki
     * @param User|null $user
     *
     * @return Response
     */
    public function exportPdfAction(Wiki $wiki, User $user = null)
    {
        $resourceNode = $wiki->getResourceNode();
        $this->checkPermission('EXPORT', $resourceNode, [], true);
        $isAdmin = $this->checkPermission('EDIT', $resourceNode);
        $sectionTree = $this->sectionManager->getSerializedSectionTree($wiki, $user, $isAdmin);
        $content = $this->templating->render(
            'IcapWikiBundle:wiki:pdf.html.twig',
            [
                '_resource' => $wiki,
                'tree' => $sectionTree,
                'isAdmin' => $isAdmin,
                'user' => $user,
            ]
        );

        return new JsonResponse([
            'content' => $content,
            'name' => $resourceNode->getName(),
        ]);
    }
}
