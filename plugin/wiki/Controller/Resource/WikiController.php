<?php

namespace Icap\WikiBundle\Controller\Resource;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Manager\SectionManager;
use JMS\DiExtraBundle\Annotation as DI;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

/**
 * @EXT\Route("/wiki", options={"expose"=true})
 */
class WikiController extends Controller
{
    use PermissionCheckerTrait;

    /** @var SectionManager */
    private $sectionManager;

    /** @var EngineInterface */
    private $templating;

    /** @var Pdf */
    private $pdfRenderer;

    /**
     * @DI\InjectParams({
     *     "sectionManager"         = @DI\Inject("Icap\WikiBundle\Manager\SectionManager"),
     *     "templating"             = @DI\Inject("templating"),
     *     "pdfRenderer"            = @DI\Inject("knp_snappy.pdf")
     * })
     *
     * SectionController constructor.
     *
     * @param SectionManager  $sectionManager
     * @param EngineInterface $templating
     * @param $pdfRenderer
     */
    public function __construct(
        SectionManager $sectionManager,
        EngineInterface $templating,
        Pdf $pdfRenderer
    ) {
        $this->sectionManager = $sectionManager;
        $this->templating = $templating;
        $this->pdfRenderer = $pdfRenderer;
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

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml(
                $content,
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
                'Content-Disposition' => 'inline; filename="'.$resourceNode->getName().'.pdf"',
            ]
        );
    }
}
