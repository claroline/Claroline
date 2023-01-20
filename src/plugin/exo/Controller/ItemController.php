<?php

namespace UJM\ExoBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Manager\Item\ItemManager;
use UJM\ExoBundle\Manager\Item\ShareManager;

/**
 * Item Controller exposes REST API.
 *
 * @Route("/quiz_questions")
 */
class ItemController extends AbstractCrudController
{
    use RequestDecoderTrait;
    use PermissionCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ItemManager */
    private $manager;
    /** @var ShareManager */
    private $shareManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ItemManager $manager,
        ShareManager $shareManager
    ) {
        $this->authorization = $authorization;
        $this->manager = $manager;
        $this->shareManager = $shareManager;
    }

    public function getName(): string
    {
        return 'quiz_questions';
    }

    public function getClass(): ?string
    {
        return Item::class;
    }

    /**
     * Shares a list of questions to users.
     *
     * @Route("/share", name="apiv2_quiz_questions_share", methods={"POST"})
     */
    public function shareAction(Request $request): JsonResponse
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
                $this->shareManager->share($data);
            } catch (InvalidDataException $e) {
                $errors = $e->getErrors();
            }
        }

        if (!empty($errors)) {
            return new JsonResponse($errors, 422);
        }

        return new JsonResponse(null, 201);
    }

    public function getIgnore(): array
    {
        return ['get', 'create', 'update', 'copyBulk', 'exist', 'find'];
    }

    protected function getDefaultHiddenFilters(): array
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        return [];
    }
}
