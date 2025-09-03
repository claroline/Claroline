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

use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Manager\PdfManager;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Manager\CourseManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/cursus_course', name: 'apiv2_cursus_course_')]
class CourseController extends AbstractCrudController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly RoutingHelper $routing,
        private readonly ToolManager $toolManager,
        private readonly CourseManager $manager,
        private readonly PdfManager $pdfManager,
    ) {
        $this->authorization = $authorization;
    }

    public static function getName(): string
    {
        return 'cursus_course';
    }

    public static function getClass(): string
    {
        return Course::class;
    }

    public static function getOptions(): array
    {
        return array_merge(parent::getOptions(), [
            'create' => [Options::PERSIST_TAG],
            'update' => [Options::PERSIST_TAG],
        ]);
    }

    #[Route(path: '/archived', name: 'list_archived', methods: ['GET'])]
    public function listArchivedAction(
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $archives = $this->crud->search(Course::class, $finderQuery->addFilters([
            'archived' => true,
        ]), [SerializerInterface::SERIALIZE_LIST]);

        return $archives->toResponse();
    }

    #[Route(path: '/list/existing', name: 'list_existing', methods: ['GET'])]
    public function listNoWorkspaceAction(
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $courses = $this->crud->search(Course::class, $finderQuery->addFilters([
            'workspace' => null,
        ]), [SerializerInterface::SERIALIZE_LIST]);

        return $courses->toResponse();
    }

    #[Route(path: '/archive', name: 'archive', methods: ['POST'])]
    public function archiveAction(Request $request): JsonResponse
    {
        $processed = [];

        $this->om->startFlushSuite();

        $data = $this->decodeRequest($request);
        /** @var Course[] $courses */
        $courses = $this->om->getRepository(Course::class)->findBy(['uuid' => $data]);

        foreach ($courses as $course) {
            if ($this->authorization->isGranted('ADMINISTRATE', $course) && !$course->isArchived()) {
                $course->setArchived(true);
                $processed[] = $course;
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (Course $course) {
            return $this->serializer->serialize($course);
        }, $processed));
    }

    #[Route(path: '/restore', name: 'restore', methods: ['POST'])]
    public function restoreAction(Request $request): JsonResponse
    {
        $processed = [];

        $this->om->startFlushSuite();

        $data = $this->decodeRequest($request);

        /** @var Course[] $courses */
        $courses = $this->om->getRepository(Course::class)->findBy([
            'uuid' => $data,
        ]);

        foreach ($courses as $course) {
            if ($this->authorization->isGranted('ADMINISTRATE', $course) && $course->isArchived()) {
                $course->setArchived(false);
                $processed[] = $course;
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (Course $course) {
            return $this->serializer->serialize($course);
        }, $processed));
    }

    #[Route(path: '/copy', name: 'copy', methods: ['POST'])]
    public function copyAction(Request $request): JsonResponse
    {
        $processed = [];

        $this->om->startFlushSuite();

        $data = $this->decodeRequest($request);

        /** @var Course[] $courses */
        $courses = $this->om->getRepository(Course::class)->findBy([
            'uuid' => $data,
        ]);

        foreach ($courses as $course) {
            if ($this->authorization->isGranted('ADMINISTRATE', $course)) {
                $copy = $this->crud->copy($course);
                $processed[] = $copy;
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (Course $course) {
            return $this->serializer->serialize($course);
        }, $processed));
    }

    #[Route(path: '/{id}/bind', name: 'bind_workspace', methods: ['PATCH'])]
    public function bindCourseToWorkspaceAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Course $course, Request $request): JsonResponse
    {
        $this->om->startFlushSuite();

        $data = $this->decodeRequest($request);

        $workspaceData = $data['workspace'] ?? null;
        $workspace = null;

        if ($workspaceData) {
            $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $workspaceData['id']]);
        }

        if ($this->authorization->isGranted('ADMINISTRATE', $course)) {
            $course->setWorkspace($workspace);
        } else {
            throw new AccessDeniedException();
        }

        $this->om->endFlushSuite();

        return new JsonResponse($this->serializer->serialize($course));
    }

    #[Route(path: '/{slug}/open', name: 'open', methods: ['GET'])]
    public function openAction(#[MapEntity(mapping: ['slug' => 'slug'])] Course $course): JsonResponse
    {
        $this->checkPermission('OPEN', $course, [], true);

        return new JsonResponse(
            $this->manager->open($course)
        );
    }

    #[Route(path: '/{id}/pdf', name: 'download_pdf', methods: ['GET'])]
    public function downloadPdfAction(#[MapEntity(mapping: ['id' => 'uuid'])] Course $course, Request $request): StreamedResponse
    {
        $this->checkPermission('OPEN', $course, [], true);

        return new StreamedResponse(function () use ($course, $request): void {
            echo $this->pdfManager->fromHtml(
                $this->manager->generateFromTemplate($course, $request->getLocale())
            );
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($course->getName()).'.pdf',
        ]);
    }

    #[Route(path: '/{id}/sessions', name: 'list_sessions', methods: ['GET'])]
    public function listSessionsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])] Course $course,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkPermission('OPEN', $course, [], true);

        $finderQuery->addFilter('course', $course->getUuid());
        $sessions = $this->crud->search(Session::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $sessions->toResponse();
    }

    #[Route(path: '/{id}/self/register', name: 'self_register', methods: ['PUT'])]
    public function selfRegisterAction(#[MapEntity(mapping: ['id' => 'uuid'])] Course $course, #[CurrentUser] ?User $user, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $course, [], true);

        if (!$course->getPendingRegistrations()) {
            throw new AccessDeniedException();
        }

        $registrationData = $this->decodeRequest($request);

        $courseUsers = $this->manager->addUsers($course, [$user], [
            $user->getUuid() => $registrationData,
        ]);

        return new JsonResponse($this->serializer->serialize($courseUsers[0]));
    }

    #[Route(path: '/{id}/stats', name: 'stats', methods: ['GET'])]
    public function getStatsAction(#[MapEntity(mapping: ['id' => 'uuid'])] Course $course): JsonResponse
    {
        $this->checkPermission('FOLLOW', $course, [], true);

        $stats = $this->om->getRepository(Course::class)->getRegistrationStats($course);

        return new JsonResponse([
            'total' => $stats['total'],
            'fields' => array_map(function (array $fieldStats) {
                return [
                    'field' => $this->serializer->serialize($fieldStats['field']),
                    'values' => $fieldStats['values'],
                ];
            }, $stats['fields']),
        ]);
    }

    private function checkToolAccess(string $rights = 'OPEN'): bool
    {
        $trainingsTool = $this->toolManager->getOrderedTool('trainings', DesktopContext::getName());

        if (is_null($trainingsTool) || !$this->authorization->isGranted($rights, $trainingsTool)) {
            return false;
        }

        return true;
    }
}
