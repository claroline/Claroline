<?php

namespace UJM\ExoBundle\Controller\Item;

use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\User\UserRepository;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UJM\ExoBundle\Manager\Item\ShareManager;

/**
 * Share Controller exposes REST API.
 *
 * @Route("/share/questions")
 */
class ShareController
{
    use RequestDecoderTrait;

    /** @var UserRepository */
    private $userRepository;

    /** @var UserSerializer */
    private $userSerializer;

    /** @var ShareManager */
    private $shareManager;

    public function __construct(
        ObjectManager $om,
        UserSerializer $userSerializer,
        ShareManager $shareManager
    ) {
        $this->userRepository = $om->getRepository(User::class);
        $this->userSerializer = $userSerializer;
        $this->shareManager = $shareManager;
    }

    /**
     * Shares a list of questions to users.
     *
     * @Route("", name="questions_share", methods={"POST"})
     * @EXT\ParamConverter("user", converter="current_user")
     */
    public function shareAction(Request $request, User $user): JsonResponse
    {
        $errors = [];

        $data = $this->decodeRequest($request);
        if (empty($data)) {
            $errors[] = [
                'path' => '',
                'message' => 'Invalid JSON data.',
            ];
        } else {
            try {
                $this->shareManager->share($data, $user);
            } catch (InvalidDataException $e) {
                $errors = $e->getErrors();
            }
        }

        if (!empty($errors)) {
            return new JsonResponse($errors, 422);
        }

        return new JsonResponse(null, 201);
    }

    /**
     * Searches users by username, first or last name.
     *
     * @Route("/{search}", name="questions_share_users", methods={"GET"})
     *
     * @param string $search
     *
     * @return JsonResponse
     */
    public function searchUsers($search)
    {
        $users = $this->userRepository->search($search, 15);

        return new JsonResponse(array_map(function (User $user) {
            return $this->userSerializer->serialize($user);
        }, $users));
    }
}
