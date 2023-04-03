<?php

namespace Claroline\CursusBundle\Controller\Registration;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Registration\CourseUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/training_course_user")
 */
class CourseUserController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    private TokenStorageInterface $tokenStorage;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
    }

    public function getName(): string
    {
        return 'training_course_user';
    }

    public function getClass(): ?string
    {
        return CourseUser::class;
    }

    /**
     * List pending users of a course.
     *
     * @Route("/{id}/pending", name="apiv2_training_course_pending_list", methods={"GET"})
     * @EXT\ParamConverter("course", class="Claroline\CursusBundle\Entity\Course", options={"mapping": {"id": "uuid"}})
     */
    public function listByCourseAction(Request $request, Course $course): JsonResponse
    {
        $this->checkPermission('REGISTER', $course, [], true);

        $params = $request->query->all();
        $params['hiddenFilters'] = $this->getDefaultHiddenFilters();
        $params['hiddenFilters']['course'] = $course->getUuid();

        return new JsonResponse(
            $this->finder->search(CourseUser::class, $params)
        );
    }

    protected function getDefaultHiddenFilters(): array
    {
        // only list participants of the same organization
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();

            // filter by organizations
            $organizations = [];
            if ($user instanceof User) {
                $organizations = $user->getOrganizations();
            }

            return [
                'organizations' => array_map(function (Organization $organization) {
                    return $organization->getUuid();
                }, $organizations),
            ];
        }

        return [];
    }

    public function getIgnore(): array
    {
        return ['get', 'exist', 'copyBulk', 'schema', 'find', 'list'];
    }
}
