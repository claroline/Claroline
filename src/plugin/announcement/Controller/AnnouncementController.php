<?php

namespace Claroline\AnnouncementBundle\Controller;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementParameters;
use Claroline\AnnouncementBundle\Manager\AnnouncementManager;
use Claroline\AnnouncementBundle\Serializer\AnnouncementSerializer;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Manager\PdfManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Manages announcements of an announcement resource.
 */
#[Route(path: '/announcement', options: ['expose' => true])]
class AnnouncementController
{
    use RequestDecoderTrait;
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly Crud $crud,
        private readonly ObjectManager $om,
        private readonly TemplateManager $templateManager,
        private readonly PdfManager $pdfManager,
        private readonly RoutingHelper $routing,
        private readonly AnnouncementManager $manager,
        private readonly AnnouncementSerializer $serializer,
    ) {
        $this->authorization = $authorization;
    }

    public static function getClass(): string
    {
        return Announcement::class;
    }

    /**
     * Creates a new announcement.
     */
    #[Route(path: '/', name: 'claro_announcement_create', methods: ['POST'])]
    public function createAction(Request $request): JsonResponse
    {
        $announcement = new Announcement();
        $this->crud->create($announcement, $this->decodeRequest($request), [Options::PERSIST_TAG]);

        return new JsonResponse(
            $this->serializer->serialize($announcement),
            201
        );
    }

    /**
     * Updates an existing announcement.
     */
    #[Route(path: '/{id}', name: 'claro_announcement_update', methods: ['PUT'])]
    public function updateAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Announcement $announcement,
        Request $request
    ): JsonResponse {
        $this->crud->update($announcement, $this->decodeRequest($request), [Options::PERSIST_TAG]);

        return new JsonResponse(
            $this->serializer->serialize($announcement)
        );
    }

    /**
     * Deletes an announcement.
     */
    #[Route(path: '/{id}', name: 'claro_announcement_delete', methods: ['DELETE'])]
    public function deleteAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Announcement $announcement
    ): JsonResponse {
        $this->checkPermission('EDIT', $announcement, [], true);

        $this->crud->delete($announcement);

        return new JsonResponse(null, 204);
    }

    /**
     * Sends an announcement (in the current implementation, it's sent by email).
     */
    #[Route(path: '/{id}/validate', name: 'claro_announcement_validate', methods: ['GET'])]
    public function validateSendAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Announcement $announcement,
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        $this->checkPermission('EDIT', $announcement, [], true);

        $finderRequest->addFilter('workspace', $announcement->getWorkspace());

        $results = $this->crud->search(User::class, $finderRequest, [SerializerInterface::SERIALIZE_LIST]);

        return $results->toResponse();
    }

    #[Route(path: '/{id}/pdf', name: 'claro_announcement_export_pdf', methods: ['GET'])]
    public function downloadPdfAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Announcement $announcement
    ): StreamedResponse {
        $this->checkPermission('EDIT', $announcement, [], true);

        $publicationDate = $announcement->getPublicationDate() ?? $announcement->getCreatedAt();
        $fileName = TextNormalizer::toKey($announcement->getTitle() ?? DateNormalizer::normalize($publicationDate));

        $workspace = $announcement->getWorkspace();

        $placeholders = array_merge([
            'title' => $announcement->getTitle(),
            'content' => $announcement->getContent(),
            'author' => $announcement->getCreator()?->getFullName(),
            'workspace_name' => $workspace->getName(),
            'workspace_code' => $workspace->getCode(),
            'workspace_url' => $this->routing->workspaceUrl($workspace),
        ], $this->templateManager->formatDatePlaceholder('publication', $publicationDate));

        $announcementParameters = $this->om->getRepository(AnnouncementParameters::class)->findOneByWorkspace($workspace);
        if ($announcementParameters->getTemplatePdf()) {
            $content = $this->templateManager->getTemplateContent($announcementParameters->getTemplatePdf(), $placeholders, '');
        } else {
            $content = $this->templateManager->getTemplate('pdf_announcement', $placeholders, '');
        }

        return new StreamedResponse(function () use ($content): void {
            echo $this->pdfManager->fromHtml($content);
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.$fileName.'.pdf',
        ]);
    }
}
