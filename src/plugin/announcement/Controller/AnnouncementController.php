<?php

namespace Claroline\AnnouncementBundle\Controller;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\AnnouncementBundle\Manager\AnnouncementManager;
use Claroline\AnnouncementBundle\Serializer\AnnouncementSerializer;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Manager\PdfManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Manages announces of an announcement resource.
 */
#[Route(path: '/announcement/{aggregateId}', options: ['expose' => true])]
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
    public function createAction(#[MapEntity(mapping: ['aggregateId' => 'uuid'])] AnnouncementAggregate $aggregate, Request $request): JsonResponse
    {
        $announcement = new Announcement();
        $announcement->setAggregate($aggregate);

        $this->crud->create($announcement, $this->decodeRequest($request), [Options::PERSIST_TAG]);

        return new JsonResponse(
            $this->serializer->serialize($announcement),
            201
        );
    }

    /**
     * Updates an existing announcement.
     *
     */
    #[Route(path: '/{id}', name: 'claro_announcement_update', methods: ['PUT'])]
    public function updateAction(#[MapEntity(mapping: ['id' => 'uuid'])] Announcement $announcement, Request $request): JsonResponse
    {
        $this->crud->update($announcement, $this->decodeRequest($request), [Options::PERSIST_TAG]);

        return new JsonResponse(
            $this->serializer->serialize($announcement)
        );
    }

    /**
     * Deletes an announcement.
     *
     */
    #[Route(path: '/{id}', name: 'claro_announcement_delete', methods: ['DELETE'])]
    public function deleteAction(
        #[MapEntity(mapping: ['aggregateId' => 'uuid'])]
        AnnouncementAggregate $aggregate,
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Announcement $announcement
    ): JsonResponse {
        $this->checkPermission('EDIT', $aggregate->getResourceNode(), [], true);

        $this->crud->delete($announcement);

        return new JsonResponse(null, 204);
    }

    /**
     * Sends an announcement (in current implementation, it's sent by email).
     *
     */
    #[Route(path: '/{id}/validate', name: 'claro_announcement_validate', methods: ['GET'])]
    public function validateSendAction(
        #[MapEntity(mapping: ['aggregateId' => 'uuid'])]
        AnnouncementAggregate $aggregate,
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Announcement $announcement, Request $request
    ): JsonResponse {
        $this->checkPermission('EDIT', $aggregate->getResourceNode(), [], true);
        $ids = isset($request->query->all()['filters']) ? $request->query->all()['filters']['roles'] : [];

        /** @var Role[] $roles */
        $roles = $this->om->getRepository(Role::class)->findBy(['uuid' => $ids]);
        $node = $announcement->getAggregate()->getResourceNode();

        $rights = $node->getRights();

        if (0 === count($roles)) {
            foreach ($rights as $right) {
                // 1 is the default "open" mask (there should be a better way to do it)
                if ($right->getMask() & 1) {
                    $roles[] = $right->getRole();
                }
            }
        }

        $all = $request->query->all();
        unset($all['filters']['roles']);
        $parameters = array_merge($all, ['hiddenFilters' => ['roles' => array_map(function (Role $role) {
            return $role->getUuid();
        }, $roles)]]);

        return new JsonResponse($this->crud->list(User::class, $parameters, [Options::SERIALIZE_MINIMAL]));
    }

    #[Route(path: '/{id}/pdf', name: 'claro_announcement_export_pdf', methods: ['GET'])]
    public function downloadPdfAction(
        #[MapEntity(mapping: ['aggregateId' => 'uuid'])]
        AnnouncementAggregate $aggregate,
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Announcement $announcement
    ): StreamedResponse {
        $this->checkPermission('EDIT', $aggregate->getResourceNode(), [], true);

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
        ], $this->templateManager->formatDatePlaceholder('publication', $publicationDate));

        if ($aggregate->getTemplatePdf()) {
            $content = $this->templateManager->getTemplateContent($aggregate->getTemplatePdf(), $placeholders, '');
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
