<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Manager\PdfManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Security\ToolPermissions;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\CursusBundle\Component\Tool\TrainingsTool;
use Claroline\CursusBundle\Entity\Event;
use Claroline\CursusBundle\Entity\EventPresence;
use Claroline\CursusBundle\Entity\Registration\EventUser;
use Claroline\CursusBundle\Manager\EventManager;
use Claroline\CursusBundle\Manager\EventPresenceManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/training_event_presence')]
class EventPresenceController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly FinderProvider $finder,
        private readonly SerializerProvider $serializer,
        private readonly EventPresenceManager $manager,
        private readonly EventManager $eventManager,
        private readonly PdfManager $pdfManager,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TranslatorInterface $translator,
        private readonly Crud $crud
    ) {
        $this->authorization = $authorization;
    }

    /**
     * Updates the status of a EventPresence for current user.
     */
    #[Route(path: '/sign', name: 'apiv2_training_event_presence_sign', methods: ['PUT'])]
    public function signStatusAction(Request $request): JsonResponse
    {
        $data = $this->decodeRequest($request);
        if (empty($data)) {
            throw new InvalidDataException('Invalid data');
        }

        $event = $data['event'];
        $signature = trim($data['signature']);

        $eventObject = $this->om->getRepository(Event::class)->findOneBy([
            'uuid' => $event['id'],
        ]);

        $presence = $this->om->getRepository(EventPresence::class)->findOneBy([
            'event' => $eventObject,
            'user' => $this->tokenStorage->getToken()?->getUser(),
        ]);

        if (!$presence) {
            return new JsonResponse(null, 404);
        }

        if (EventPresence::PRESENT === $presence->getStatus()) {
            return new JsonResponse(['success' => false]);
        }

        $presence->setStatus(EventPresence::PRESENT);
        $presence->setSignature($signature);
        $presence->setUpdatedBy($this->tokenStorage->getToken()->getUser());
        $presence->setUpdatedAt(new \DateTime());

        $this->om->persist($presence);
        $this->om->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * Confirm the status of a EventPresence for current event.
     */
    #[Route(path: '/confirm/{id}', name: 'apiv2_training_event_presence_confirm', methods: ['PUT'])]
    public function confirmStatusAction(#[MapEntity(mapping: ['id' => 'uuid'])] Event $event): JsonResponse
    {
        $this->checkPermission('ADMINISTRATE', $event, [], true);

        $presences = $this->om->getRepository(EventPresence::class)->findBy(['event' => $event]);
        $presencesToValidate = array_filter($presences, function (EventPresence $presence) {
            return EventPresence::UNKNOWN !== $presence->getStatus();
        });

        $this->om->startFlushSuite();

        foreach ($presencesToValidate as $presence) {
            $this->manager->setValidationDate([$presence], new \DateTime());
        }

        $this->om->endFlushSuite();

        return new JsonResponse();
    }

    #[Route(path: '/check/{code}', name: 'apiv2_training_event_presence_check', methods: ['GET'])]
    public function getEventPresenceByCodeAction(string $code): JsonResponse
    {
        $event = $this->om->getRepository(Event::class)->findOneBy(['code' => $code]);
        if (!$event) {
            return new JsonResponse(null, 404);
        }

        $user = $this->tokenStorage->getToken()?->getUser();
        if (!$user) {
            return new JsonResponse(null, 401);
        }

        $eventUser = $this->om->getRepository(EventUser::class)->findOneBy([
            'user' => $user,
            'event' => $event,
        ]);
        if (!$eventUser) {
            return new JsonResponse($this->serializer->serialize($event), 403);
        }

        $presence = $this->om->getRepository(EventPresence::class)->findOneBy([
            'event' => $event,
            'user' => $user,
        ]);

        return new JsonResponse($this->serializer->serialize($presence));
    }

    #[Route(path: '/{id}', name: 'apiv2_training_event_presence_list', methods: ['GET'])]
    public function listAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Event $event,
        Request $request
    ): JsonResponse {
        $this->checkPermission('OPEN', $event, [], true);

        // not optimal, as it will do it for each new search
        $this->manager->generate($event, $this->eventManager->getRegisteredUsers($event));

        $params = $request->query->all();
        $params['hiddenFilters'] = [
            'event' => $event->getUuid(),
        ];

        return new JsonResponse(
            $this->finder->search(EventPresence::class, $params)
        );
    }

    #[Route(path: '/workspace/{id}', name: 'apiv2_cursus_workspace_presence_list', methods: ['GET'])]
    public function listByWorkspaceAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Workspace $workspace, Request $request
    ): JsonResponse {
        $isManager = $this->checkPermission(ToolPermissions::getPermission(TrainingsTool::getName(), 'EDIT'), $workspace)
            || $this->checkPermission(ToolPermissions::getPermission(TrainingsTool::getName(), 'FOLLOW'), $workspace);

        $params = $request->query->all();
        $params['hiddenFilters'] = [
            'workspace' => $workspace->getUuid(),
        ];

        if (!$isManager) {
            $params['hiddenFilters']['user'] = $this->tokenStorage->getToken()?->getUser()->getUuid();
        }

        return new JsonResponse(
            $this->finder->search(EventPresence::class, $params)
        );
    }

    /**
     * Updates the status of an EventPresence list.
     */
    #[Route(path: '/status/{status}', name: 'apiv2_training_event_presence_update', methods: ['PUT'])]
    public function updateStatusAction(string $status, Request $request): JsonResponse
    {
        $data = $this->decodeRequest($request);
        if (empty($data)) {
            return new JsonResponse(null, 404);
        }

        $presences = $this->om->getRepository(EventPresence::class)->findBy(['uuid' => $data]);
        foreach ($presences as $presence) {
            $this->checkPermission('EDIT', $presence, [], true);

            $presence->setUpdatedBy($this->tokenStorage->getToken()->getUser());
            $presence->setUpdatedAt(new \DateTime());

            $this->crud->update($presence, ['status' => $status], [Crud::NO_PERMISSIONS]);
        }

        return new JsonResponse(array_map(function (EventPresence $presence) {
            return $this->serializer->serialize($presence);
        }, $presences));
    }

    #[Route(path: '/{id}/download/{filled}', name: 'apiv2_training_event_presence_download', methods: ['GET'])]
    public function downloadPdfAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Event $event,
        Request $request,
        int $filled
    ): StreamedResponse {
        $this->checkPermission('EDIT', $event, [], true);

        return new StreamedResponse(function () use ($event, $request, $filled): void {
            echo $this->pdfManager->fromHtml(
                $this->manager->download($event, $this->eventManager->getRegisteredUsers($event), $request->getLocale(), (bool) $filled)
            );
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($event->getName()).'-presences.pdf',
        ]);
    }

    #[Route(path: '/{id}/pdf', name: 'apiv2_cursus_user_presence_download', methods: ['GET'])]
    public function downloadUserPdfAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        EventPresence $eventPresence,
        Request $request
    ): StreamedResponse {
        $this->checkPermission('OPEN', $eventPresence, [], true);

        return new StreamedResponse(function () use ($eventPresence, $request): void {
            echo $this->pdfManager->fromHtml(
                $this->manager->downloadUser($eventPresence, $request->getLocale())
            );
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($eventPresence->getEvent()->getName()).'-presence.pdf',
        ]);
    }

    #[Route(path: '/{id}/evidence', name: 'apiv2_cursus_presence_evidence_upload', methods: ['POST'])]
    public function uploadEvidenceAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        EventPresence $eventPresence,
        Request $request
    ): JsonResponse {
        $this->checkPermission('EDIT', $eventPresence, [], true);

        $files = $request->files->all();

        $evidence = [];
        foreach ($files as $index => $file) {
            $evidenceFile = $this->manager->uploadEvidence($file, $eventPresence);
            $evidence[] = [
                'type' => $evidenceFile->getMimeType(),
                'mimeType' => $evidenceFile->getMimeType(),
                'name' => $evidenceFile->getFilename(),
                'size' => $evidenceFile->getSize(),
                'url' => $evidenceFile->getRealPath(),
                'num' => $index + 1,
            ];
        }

        $eventPresence->setEvidence($evidence);
        $eventPresence->setEvidenceAddedBy($this->tokenStorage->getToken()->getUser());
        $eventPresence->setEvidenceAddedAt(new \DateTime());

        $this->om->persist($eventPresence);
        $this->om->flush();

        return new JsonResponse($this->serializer->serialize($eventPresence));
    }

    #[Route(path: '/{id}/evidence', name: 'apiv2_cursus_presence_evidence_delete', methods: ['DELETE'])]
    public function deleteEvidenceAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        EventPresence $eventPresence
    ): JsonResponse {
        $this->checkPermission('EDIT', $eventPresence, [], true);

        $eventPresence->setEvidence(null);

        $this->om->persist($eventPresence);
        $this->om->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route(path: '/{id}/evidence', name: 'apiv2_cursus_presence_evidence_download', methods: ['GET'])]
    public function downloadEvidenceAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        EventPresence $eventPresence,
        Request $request
    ): StreamedResponse {
        $this->checkPermission('OPEN', $eventPresence, [], true);

        $file = $request->query->all()['file'];
        $content = file_get_contents($file['url']);
        $downloadedName = $this->translator->trans('evidence', [], 'presence').'-'.$file['num'].'-'.$eventPresence->getUser()->getUsername().'-'.$eventPresence->getEvent()->getCode();
        $extension = pathinfo($file['url'], \PATHINFO_EXTENSION);

        return new StreamedResponse(function () use ($content): void {
            echo $content;
        }, 200, [
            'Content-Type' => $file['type'],
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($downloadedName).'.'.$extension,
        ]);
    }
}
