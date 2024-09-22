<?php

namespace Claroline\CursusBundle\Controller\Registration;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Registration\CourseUser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/training_course_user', name: 'apiv2_training_course_user_')]
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

    public static function getName(): string
    {
        return 'training_course_user';
    }

    public static function getClass(): string
    {
        return CourseUser::class;
    }

    public function getIgnore(): array
    {
        return ['get', 'list'];
    }

    /**
     * List pending users of a course.
     *
     */
    #[Route(path: '/{id}/pending', name: 'list', methods: ['GET'])]
    public function listByCourseAction(Request $request, #[MapEntity(class: 'Claroline\CursusBundle\Entity\Course', mapping: ['id' => 'uuid'])]
    Course $course): JsonResponse
    {
        $this->checkPermission('REGISTER', $course, [], true);

        $params = $request->query->all();
        $params['hiddenFilters'] = $this->getDefaultHiddenFilters();
        $params['hiddenFilters']['course'] = $course->getUuid();

        return new JsonResponse(
            $this->crud->list(CourseUser::class, $params)
        );
    }

    protected function getDefaultHiddenFilters(): array
    {
        // only list participants of the same organization
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            /** @var User $user */
            $user = $this->tokenStorage->getToken()?->getUser();

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
}
