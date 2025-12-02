<?php

namespace Claroline\ForumBundle\Controller;

use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Manager\ForumManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/forum', name: 'apiv2_forum_')]
class ForumController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ForumManager $manager
    ) {
        $this->authorization = $authorization;
    }

    public static function getClass(): string
    {
        return Forum::class;
    }

    public static function getName(): string
    {
        return 'forum';
    }

    #[Route(path: '/{id}/subjects', name: 'list_subjects', methods: ['GET'])]
    public function listSubjectsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Forum $forum,
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        $this->checkPermission('OPEN', $forum->getResourceNode(), [], true);

        $finderRequest->addFilter('forum', $forum->getUuid());

        $subjects = $this->crud->search(Subject::class, $finderRequest, [SerializerInterface::SERIALIZE_LIST]);

        return $subjects->toResponse();
    }

    #[Route(path: '/{id}/subject', name: 'create_subject', methods: ['POST', 'PUT'])]
    public function createSubjectAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Forum $forum,
        Request $request
    ): JsonResponse {
        $subject = new Subject();
        $subject->setForum($forum);

        $options = static::getOptions();
        $this->crud->create($subject, $this->decodeRequest($request), $options['create'] ?? []);

        return new JsonResponse(
            $this->serializer->serialize($subject, $options['get'] ?? []),
            201
        );
    }

    #[Route(path: '/notify/{user}/forum/{forum}', name: 'notify', methods: ['PATCH'])]
    public function notifyAction(
        #[MapEntity(mapping: ['user' => 'uuid'])]
        User $user,
        #[MapEntity(mapping: ['forum' => 'uuid'])]
        Forum $forum
    ): JsonResponse {
        $this->checkPermission('OPEN', $forum->getResourceNode(), [], true);

        $validationUser = $this->manager->getValidationUser($user, $forum);
        $validationUser->setNotified(true);
        $this->om->persist($validationUser);
        $this->om->flush();

        return new JsonResponse(true);
    }

    #[Route(path: '/unnotify/{user}/forum/{forum}', name: 'unnotifiy', methods: ['PATCH'])]
    public function unnotifyAction(
        #[MapEntity(mapping: ['user' => 'uuid'])]
        User $user,
        #[MapEntity(mapping: ['forum' => 'uuid'])]
        Forum $forum
    ): JsonResponse {
        $this->checkPermission('OPEN', $forum->getResourceNode(), [], true);

        $validationUser = $this->manager->getValidationUser($user, $forum);
        $validationUser->setNotified(false);
        $this->om->persist($validationUser);
        $this->om->flush();

        return new JsonResponse(true);
    }
}
