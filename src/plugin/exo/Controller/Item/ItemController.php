<?php

namespace UJM\ExoBundle\Controller\Item;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Manager\Item\ItemManager;

/**
 * Item Controller exposes REST API.
 *
 * @Route("/questions")
 *
 * @todo : use a crud controller instead
 */
class ItemController
{
    use RequestDecoderTrait;

    /** @var FinderProvider */
    private $finder;

    /** @var ItemManager */
    private $manager;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * ItemController constructor.
     */
    public function __construct(
        FinderProvider $finder,
        ItemManager $manager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->finder = $finder;
        $this->manager = $manager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Searches for questions.
     *
     * @Route("", name="question_list", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        return new JsonResponse(
            $this->finder->search(
                'UJM\ExoBundle\Entity\Item\Item',
                $request->query->all(),
                [Transfer::INCLUDE_SOLUTIONS, Transfer::INCLUDE_ADMIN_META]
            )
        );
    }

    /**
     * Creates a new Item.
     *
     * @Route("", name="question_create", methods={"POST"})
     *
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        $errors = [];
        $question = null;

        $data = $this->decodeRequest($request);
        if (empty($data)) {
            // Invalid or empty JSON data received
            $errors[] = [
                'path' => '',
                'message' => 'Invalid JSON data',
            ];
        } else {
            // Try to update question with data
            try {
                $question = $this->manager->create($data);
            } catch (InvalidDataException $e) {
                $errors = $e->getErrors();
            }
        }

        if (empty($errors)) {
            // Item updated
            return new JsonResponse(
                $this->manager->serialize($question, [Transfer::INCLUDE_SOLUTIONS, Transfer::INCLUDE_ADMIN_META])
            );
        } else {
            // Invalid data received
            return new JsonResponse($errors, 422);
        }
    }

    /**
     * Updates a Item.
     *
     * @Route("/{id}", name="question_update", methods={"PUT"})
     * @EXT\ParamConverter("question", class="UJM\ExoBundle\Entity\Item\Item", options={"mapping": {"id": "uuid"}})
     *
     * @return JsonResponse
     */
    public function updateAction(Item $question, Request $request)
    {
        $errors = [];

        $data = $this->decodeRequest($request);
        if (empty($data)) {
            // Invalid or empty JSON data received
            $errors[] = [
                'path' => '',
                'message' => 'Invalid JSON data',
            ];
        } else {
            // Try to update question with data
            try {
                $question = $this->manager->update($question, $data);
            } catch (InvalidDataException $e) {
                $errors = $e->getErrors();
            }
        }

        if (empty($errors)) {
            // Item updated
            return new JsonResponse(
                $this->manager->serialize($question, [Transfer::INCLUDE_SOLUTIONS, Transfer::INCLUDE_ADMIN_META])
            );
        } else {
            // Invalid data received
            return new JsonResponse($errors, 422);
        }
    }

    /**
     * Duplicates a list of items.
     *
     * @Route("/{id}", name="questions_duplicate", methods={"POST"})
     */
    public function duplicateBulkAction(Request $request)
    {
    }

    /**
     * Deletes a list of Items.
     *
     * @Route("/{id}", name="questions_delete", methods={"DELETE"})
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, User $user)
    {
        $errors = [];

        $data = $this->decodeRequest($request);
        if (empty($data) || !is_array($data)) {
            // Invalid or empty JSON data received
            $errors[] = [
                'path' => '',
                'message' => 'Invalid JSON data',
            ];
        } else {
            try {
                $this->manager->deleteBulk(json_decode($request->getContent(), true), $user);
            } catch (\Exception $e) {
                return new JsonResponse($e->getMessage(), 422);
            }
        }

        return new JsonResponse(null, 204);
    }
}
