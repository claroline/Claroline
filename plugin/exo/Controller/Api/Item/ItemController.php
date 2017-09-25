<?php

namespace UJM\ExoBundle\Controller\Api\Item;

use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UJM\ExoBundle\Controller\Api\AbstractController;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Validator\ValidationException;
use UJM\ExoBundle\Manager\Item\ItemManager;

/**
 * Item Controller exposes REST API.
 *
 * @EXT\Route("/questions", options={"expose"=true})
 */
class ItemController extends AbstractController
{
    /**
     * @var ItemManager
     */
    private $itemManager;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * ItemController constructor.
     *
     * @DI\InjectParams({
     *     "itemManager"     = @DI\Inject("ujm_exo.manager.item"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher")
     * })
     *
     * @param ItemManager              $itemManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ItemManager $itemManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->itemManager = $itemManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Searches for questions.
     *
     * @EXT\Route("/search", name="question_search")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchAction(User $user, Request $request)
    {
        $searchParams = $this->decodeRequestData($request);

        return new JsonResponse(
            $this->itemManager->search($user, $searchParams->filters)
        );
    }

    /**
     * Creates a new Item.
     *
     * @EXT\Route("", name="question_create")
     * @EXT\Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        $errors = [];
        $question = null;

        $data = $this->decodeRequestData($request);
        if (empty($data)) {
            // Invalid or empty JSON data received
            $errors[] = [
                'path' => '',
                'message' => 'Invalid JSON data',
            ];
        } else {
            // Try to update question with data
            try {
                $question = $this->itemManager->create($data);
            } catch (ValidationException $e) {
                $errors = $e->getErrors();
            }
        }

        if (empty($errors)) {
            // Item updated
            return new JsonResponse(
                $this->itemManager->serialize($question, [Transfer::INCLUDE_SOLUTIONS, Transfer::INCLUDE_ADMIN_META])
            );
        } else {
            // Invalid data received
            return new JsonResponse($errors, 422);
        }
    }

    /**
     * Updates a Item.
     *
     * @EXT\Route("/{id}", name="question_update")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("question", class="UJMExoBundle:Item\Item", options={"mapping": {"id": "uuid"}})
     *
     * @param Item    $question
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAction(Item $question, Request $request)
    {
        $errors = [];

        $data = $this->decodeRequestData($request);
        if (empty($data)) {
            // Invalid or empty JSON data received
            $errors[] = [
                'path' => '',
                'message' => 'Invalid JSON data',
            ];
        } else {
            // Try to update question with data
            try {
                $question = $this->itemManager->update($question, $data);
            } catch (ValidationException $e) {
                $errors = $e->getErrors();
            }
        }

        if (empty($errors)) {
            // Item updated
            return new JsonResponse(
                $this->itemManager->serialize($question, [Transfer::INCLUDE_SOLUTIONS, Transfer::INCLUDE_ADMIN_META])
            );
        } else {
            // Invalid data received
            return new JsonResponse($errors, 422);
        }
    }

    /**
     * Deletes a Item.
     *
     * @EXT\Route("/{id}", name="question_delete")
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param Request $request
     * @param User    $user
     *
     * @return JsonResponse
     */
    public function deleteAction(Request $request, User $user)
    {
        $errors = [];

        $data = $this->decodeRequestData($request);
        if (empty($data) || !is_array($data)) {
            // Invalid or empty JSON data received
            $errors[] = [
                'path' => '',
                'message' => 'Invalid JSON data',
            ];
        } else {
            try {
                $this->itemManager->delete($data, $user);
            } catch (ValidationException $e) {
                $errors = $e->getErrors();
            }
        }

        return new JsonResponse(null, 204);
    }
}
