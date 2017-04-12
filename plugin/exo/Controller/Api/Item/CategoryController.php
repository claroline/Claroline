<?php

namespace UJM\ExoBundle\Controller\Api\Item;

use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UJM\ExoBundle\Controller\Api\AbstractController;
use UJM\ExoBundle\Entity\Item\Category;
use UJM\ExoBundle\Library\Validator\ValidationException;
use UJM\ExoBundle\Manager\Item\CategoryManager;

/**
 * Category API Controller exposes REST API.
 *
 * @EXT\Route("/categories", options={"expose"=true})
 */
class CategoryController extends AbstractController
{
    /**
     * @var CategoryManager
     */
    private $categoryManager;

    /**
     * CategoryController constructor.
     *
     * @DI\InjectParams({
     *     "categoryManager" = @DI\Inject("ujm_exo.manager.category")
     * })
     *
     * @param CategoryManager $categoryManager
     */
    public function __construct(CategoryManager $categoryManager)
    {
        $this->categoryManager = $categoryManager;
    }

    /**
     * Lists all categories of a user.
     *
     * @EXT\Route("", name="question_category_list")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    public function listAction(User $user)
    {
        $categories = $this->categoryManager->all($user);

        return new JsonResponse(array_map(function (Category $category) {
            return $this->categoryManager->serialize($category);
        }, $categories));
    }

    /**
     * Creates a new category.
     *
     * @EXT\Route("", name="question_category_create")
     * @EXT\Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        $category = null;
        $errors = [];

        $data = $this->decodeRequestData($request);
        if (null === $data) {
            $errors[] = [
                'path' => '',
                'message' => 'Invalid JSON data',
            ];
        } else {
            // Try to create category
            try {
                $category = $this->categoryManager->create($data);
            } catch (ValidationException $e) {
                $errors = $e->getErrors();
            }
        }

        if (empty($errors)) {
            // Category created
            return new JsonResponse($this->categoryManager->serialize($category), 201);
        } else {
            // Invalid data received
            return new JsonResponse($errors, 422);
        }
    }

    /**
     * Updates a category.
     *
     * @EXT\Route("/{id}", name="question_category_update")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\ParamConverter("category", class="UJMExoBundle:Item\Category", options={"mapping": {"id": "uuid"}})
     *
     * @param User     $user
     * @param Category $category
     * @param Request  $request
     *
     * @return JsonResponse
     */
    public function updateAction(User $user, Category $category, Request $request)
    {
        $this->assertIsAdmin($user, $category);

        $errors = [];

        $data = $this->decodeRequestData($request);
        if (null === $data) {
            $errors[] = [
                'path' => '',
                'message' => 'Invalid JSON data',
            ];
        } else {
            // Try to update exercise
            try {
                $this->categoryManager->update($category, $data);
            } catch (ValidationException $e) {
                $errors = $e->getErrors();
            }
        }

        if (empty($errors)) {
            // Category updated
            return new JsonResponse($this->categoryManager->serialize($category));
        } else {
            // Invalid data received
            return new JsonResponse($errors, 422);
        }
    }

    /**
     * Deletes a category.
     *
     * @EXT\Route("/{id}", name="question_category_delete")
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\ParamConverter("category", class="UJMExoBundle:Item\Category", options={"mapping": {"id": "uuid"}})
     *
     * @param User     $user
     * @param Category $category
     *
     * @return JsonResponse
     */
    public function deleteAction(User $user, Category $category)
    {
        $this->assertIsAdmin($user, $category);

        try {
            $this->categoryManager->delete($category);
        } catch (ValidationException $e) {
            return new JsonResponse($e->getErrors(), 422);
        }

        return new JsonResponse(null, 204);
    }

    /**
     * Checks the category belongs to the user.
     *
     * @param User     $user
     * @param Category $category
     *
     * @throws AccessDeniedException
     */
    private function assertIsAdmin(User $user, Category $category)
    {
        if ($user->getId() !== $category->getUser()->getId()) {
            throw new AccessDeniedException();
        }
    }
}
