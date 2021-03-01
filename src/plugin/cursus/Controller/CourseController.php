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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Registration\SessionGroup;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Manager\CourseManager;
use Dompdf\Dompdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/cursus_course")
 */
class CourseController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var ToolManager */
    private $toolManager;
    /** @var CourseManager */
    private $manager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        PlatformConfigurationHandler $config,
        ToolManager $toolManager,
        CourseManager $manager
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->config = $config;
        $this->toolManager = $toolManager;
        $this->manager = $manager;
    }

    public function getName()
    {
        return 'cursus_course';
    }

    public function getClass()
    {
        return Course::class;
    }

    public function getIgnore()
    {
        return ['copyBulk', 'schema'];
    }

    public function getOptions()
    {
        return array_merge(parent::getOptions(), [
            'create' => [Options::PERSIST_TAG],
            'update' => [Options::PERSIST_TAG],
        ]);
    }

    protected function getDefaultHiddenFilters()
    {
        $filters = [];
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();

            // filter by organizations
            $filters['organizations'] = array_map(function (Organization $organization) {
                return $organization->getUuid();
            }, $user->getOrganizations());

            // hide hidden trainings for non admin
            if (!$this->checkToolAccess('EDIT')) {
                $filters['hidden'] = false;
            }
        }

        return $filters;
    }

    /**
     * @Route("/{slug}/open", name="apiv2_cursus_course_open", methods={"GET"})
     * @EXT\ParamConverter("course", class="ClarolineCursusBundle:Course", options={"mapping": {"slug": "slug"}})
     */
    public function openAction(Course $course): JsonResponse
    {
        $this->checkPermission('OPEN', $course, [], true);

        $user = $this->tokenStorage->getToken()->getUser();
        $registrations = [];
        if ($user instanceof User) {
            $registrations = [
                'users' => $this->finder->search(SessionUser::class, ['filters' => [
                    'user' => $user->getUuid(),
                    'course' => $course->getUuid(),
                ]])['data'],
                'groups' => $this->finder->search(SessionGroup::class, ['filters' => [
                    'user' => $user->getUuid(),
                    'course' => $course->getUuid(),
                ]])['data'],
            ];
        }

        $sessions = $this->finder->search(Session::class, [
            'filters' => [
                'terminated' => false,
                'course' => $course->getUuid(),
            ],
        ]);

        return new JsonResponse([
            'course' => $this->serializer->serialize($course),
            'defaultSession' => $course->getDefaultSession() ? $this->serializer->serialize($course->getDefaultSession()) : null,
            'availableSessions' => $sessions['data'],
            'registrations' => $registrations,
        ]);
    }

    /**
     * @Route("/{id}/pdf", name="apiv2_cursus_course_download_pdf", methods={"GET"})
     * @EXT\ParamConverter("course", class="ClarolineCursusBundle:Course", options={"mapping": {"id": "uuid"}})
     */
    public function downloadPdfAction(Course $course, Request $request): StreamedResponse
    {
        $this->checkPermission('OPEN', $course, [], true);

        $domPdf = new Dompdf([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'tempDir' => $this->config->getParameter('server.tmp_dir'),
        ]);

        $domPdf->loadHtml($this->manager->generateFromTemplate($course, $request->getLocale()));

        // Render the HTML as PDF
        $domPdf->render();

        return new StreamedResponse(function () use ($domPdf) {
            echo $domPdf->output();
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($course->getName()).'.pdf',
        ]);
    }

    /**
     * @Route("/{id}/sessions", name="apiv2_cursus_course_list_sessions", methods={"GET"})
     * @EXT\ParamConverter("course", class="ClarolineCursusBundle:Course", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function listSessionsAction(User $user, Course $course, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $course, [], true);

        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['course'] = $course->getUuid();

        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            $params['hiddenFilters']['organizations'] = array_map(function (Organization $organization) {
                return $organization->getUuid();
            }, $user->getAdministratedOrganizations()->toArray());
        }

        return new JsonResponse(
            $this->finder->search(Session::class, $params)
        );
    }

    private function checkToolAccess(string $rights = 'OPEN')
    {
        $trainingsTool = $this->toolManager->getOrderedTool('trainings', Tool::DESKTOP);

        if (is_null($trainingsTool) || !$this->authorization->isGranted($rights, $trainingsTool)) {
            throw new AccessDeniedException();
        }
    }
}
