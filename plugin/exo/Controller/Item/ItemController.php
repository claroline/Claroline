<?php

namespace UJM\ExoBundle\Controller\Item;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UJM\ExoBundle\Controller\AbstractController;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Manager\Item\ItemManager;

/**
 * Item Controller exposes REST API.
 *
 * @EXT\Route("/questions", options={"expose"=true})
 *
 * @todo : use a crud controller instead
 */
class ItemController extends AbstractController
{
    /** @var FinderProvider */
    private $finder;

    /** @var ItemManager */
    private $manager;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * ItemController constructor.
     *
     * @param FinderProvider           $finder
     * @param ItemManager              $manager
     * @param EventDispatcherInterface $eventDispatcher
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
     * @EXT\Route("", name="question_list")
     * @EXT\Method("GET")
     *
     * @param Request $request
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
     * @EXT\Route("/{id}", name="questions_duplicate")
     * @EXT\Method("POST")
     *
     * @param Request $request
     */
    public function duplicateBulkAction(Request $request)
    {
    }

    /**
     * Deletes a list of Items.
     *
     * @EXT\Route("/{id}", name="questions_delete")
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param Request $request
     * @param User    $user
     *
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, User $user)
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
                $this->manager->deleteBulk(json_decode($request->getContent(), true), $user);
            } catch (\Exception $e) {
                return new JsonResponse($e->getMessage(), 422);
            }
        }

        return new JsonResponse(null, 204);
    }
}
