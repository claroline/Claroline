<?php

namespace Claroline\AnnouncementBundle\Controller;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Manager\PdfManager;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Manages announces of an announcement resource.
 *
 * @Route("/announcement/{aggregateId}", options={"expose"=true})
 *
 * @EXT\ParamConverter("aggregate", class="Claroline\AnnouncementBundle\Entity\AnnouncementAggregate", options={"mapping": {"aggregateId": "uuid"}})
 */
class AnnouncementAggregateController
{
    use RequestDecoderTrait;

    /** @var Crud */
    private $crud;

    /** @var SerializerProvider */
    private $serializer;

    /** @var TemplateManager */
    private $templateManager;

    /** @var PdfManager */
    private $pdfManager;

    /** @var RoutingHelper */
    private $routing;

    public function __construct(
        Crud $crud,
        SerializerProvider $serializer,
        TemplateManager $templateManager,
        PdfManager $pdfManager,
        RoutingHelper $routing
    ) {
        $this->crud = $crud;
        $this->serializer = $serializer;
        $this->templateManager = $templateManager;
        $this->pdfManager = $pdfManager;
        $this->routing = $routing;
    }

    public function getClass(): string
    {
        return AnnouncementAggregate::class;
    }

    /**
     * Updates an existing announce.
     *
     * @Route("/", name="claro_announcement_aggregate_update", methods={"PUT"})
     */
    public function updateAction(AnnouncementAggregate $aggregate, Request $request): JsonResponse
    {
        $this->crud->update($aggregate, $this->decodeRequest($request), [Crud::THROW_EXCEPTION]);

        return new JsonResponse(
            $this->serializer->serialize($aggregate)
        );
    }

    /**
     * @Route("/pdf/{announcementId}", name="claro_announcement_export_pdf", methods={"GET"})
     *
     * @EXT\ParamConverter("announcement", class="Claroline\AnnouncementBundle\Entity\Announcement", options={"mapping": {"announcementId": "uuid"}})
     */
    public function downloadPdfAction(AnnouncementAggregate $aggregate, Announcement $announcement): StreamedResponse
    {
        $fileName = TextNormalizer::toKey($aggregate->getResourceNode()->getName());

        $workspace = $aggregate->getResourceNode()->getWorkspace();
        $publicationDate = $announcement->getPublicationDate() ?? $announcement->getCreationDate();

        $placeholders = array_merge([
            'title' => $announcement->getTitle(),
            'content' => $announcement->getContent(),
            'author' => $announcement->getAnnouncer() ?: $announcement->getCreator()->getFullName(),
            'workspace_name' => $workspace->getName(),
            'workspace_code' => $workspace->getCode(),
            'workspace_url' => $this->routing->workspaceUrl($workspace),
        ], $this->templateManager->formatDatePlaceholder('publication', $publicationDate)
        );

        if ($aggregate->getTemplatePdf()) {
            $content = $this->templateManager->getTemplateContent($aggregate->getTemplatePdf(), $placeholders, '');
        } else {
            $content = $this->templateManager->getTemplate('pdf_announcement', $placeholders, '');
        }

        return new StreamedResponse(function () use ($content) {
            echo $this->pdfManager->fromHtml($content);
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.$fileName.'.pdf',
        ]);
    }
}
