<?php

namespace UJM\ExoBundle\Controller\Api\Item;

use Claroline\CoreBundle\API\FinderProvider;
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
    /** @var FinderProvider */
    private $finder;

    /** @var ItemManager */
    private $manager;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * ItemController constructor.
     *
     * @DI\InjectParams({
     *     "finder"  = @DI\Inject("claroline.api.finder"),
     *     "manager" = @DI\Inject("ujm_exo.manager.item"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher")
     * })
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
                [Transfer::INCLUDE_ADMIN_META]
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
            } catch (ValidationException $e) {
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
            } catch (ValidationException $e) {
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
                $this->manager->deleteBulk(json_decode($request->getContent()), $user);
            } catch (\Exception $e) {
                return new JsonResponse($e->getMessage(), 422);
            }
        }

        return new JsonResponse(null, 204);
    }
}
