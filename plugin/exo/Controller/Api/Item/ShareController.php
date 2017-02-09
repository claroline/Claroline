<?php

namespace UJM\ExoBundle\Controller\Api\Item;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Repository\UserRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UJM\ExoBundle\Controller\Api\AbstractController;
use UJM\ExoBundle\Library\Validator\ValidationException;
use UJM\ExoBundle\Manager\Item\ShareManager;
use UJM\ExoBundle\Serializer\UserSerializer;

/**
 * Share Controller exposes REST API.
 *
 * @EXT\Route("/questions/share", options={"expose"=true})
 */
class ShareController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserSerializer
     */
    private $userSerializer;

    /**
     * @var ShareManager
     */
    private $shareManager;

    /**
     * ShareController constructor.
     *
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "userSerializer" = @DI\Inject("ujm_exo.serializer.user"),
     *     "shareManager"   = @DI\Inject("ujm_exo.manager.share")
     * })
     *
     * @param ObjectManager  $om
     * @param UserSerializer $userSerializer
     * @param ShareManager   $shareManager
     */
    public function __construct(
        ObjectManager $om,
        UserSerializer $userSerializer,
        ShareManager $shareManager)
    {
        $this->userRepository = $om->getRepository('ClarolineCoreBundle:User');
        $this->userSerializer = $userSerializer;
        $this->shareManager = $shareManager;
    }

    /**
     * Shares a list of questions to users.
     *
     * @EXT\Route("", name="questions_share")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param Request $request
     * @param User    $user
     *
     * @return JsonResponse
     */
    public function shareAction(Request $request, User $user)
    {
        $errors = [];

        $data = $this->decodeRequestData($request);
        if (empty($data)) {
            $errors[] = [
                'path' => '',
                'message' => 'Invalid JSON data.',
            ];
        } else {
            try {
                $this->shareManager->share($data, $user);
            } catch (ValidationException $e) {
                $errors = $e->getErrors();
            }
        }

        if (!empty($errors)) {
            return new JsonResponse($errors, 422);
        } else {
            return new JsonResponse(null, 201);
        }
    }

    /**
     * @EXT\Route("", name="question_share_update")
     * @EXT\Method("DELETE")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAction(Request $request)
    {
    }

    /**
     * @EXT\Route("", name="question_share_delete")
     * @EXT\Method("DELETE")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deleteAction(Request $request)
    {
    }

    /**
     * Searches users by username, first or last name.
     *
     * @EXT\Route("/{search}", name="questions_share_users")
     * @EXT\Method("GET")
     *
     * @param string $search
     *
     * @return JsonResponse
     */
    public function searchUsers($search)
    {
        $users = $this->userRepository->findByName($search);

        return new JsonResponse(array_map(function (User $user) {
            return $this->userSerializer->serialize($user);
        }, $users));
    }
}
