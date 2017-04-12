<?php

namespace UJM\ExoBundle\Manager\Item;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Item\Category;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\ValidationException;
use UJM\ExoBundle\Repository\CategoryRepository;
use UJM\ExoBundle\Serializer\Item\CategorySerializer;
use UJM\ExoBundle\Validator\JsonSchema\Item\CategoryValidator;

/**
 * Manages item categories.
 *
 * @DI\Service("ujm_exo.manager.category")
 */
class CategoryManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var CategoryRepository
     */
    private $repository;

    /**
     * @var CategoryValidator
     */
    private $validator;

    /**
     * @var CategorySerializer
     */
    private $serializer;

    /**
     * CategoryManager constructor.
     *
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "validator"  = @DI\Inject("ujm_exo.validator.category"),
     *     "serializer" = @DI\Inject("ujm_exo.serializer.category")
     * })
     *
     * @param ObjectManager      $om
     * @param CategoryValidator  $validator
     * @param CategorySerializer $serializer
     */
    public function __construct(
        ObjectManager $om,
        CategoryValidator $validator,
        CategorySerializer $serializer)
    {
        $this->om = $om;
        $this->repository = $this->om->getRepository('UJMExoBundle:Item\Category');
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    /**
     * Lists all the categories of a User.
     *
     * @param User $user
     *
     * @return Category[]
     */
    public function all(User $user)
    {
        return $this->repository->findBy([
            'user' => $user,
        ]);
    }

    /**
     * Validates and creates a new Category from raw data.
     *
     * @param \stdClass $data
     *
     * @return Category
     *
     * @throws ValidationException
     */
    public function create(\stdClass $data)
    {
        return $this->update(new Category(), $data);
    }

    /**
     * Validates and updates a Category entity with raw data.
     *
     * @param Category  $category
     * @param \stdClass $data
     *
     * @return Category
     *
     * @throws ValidationException
     */
    public function update(Category $category, \stdClass $data)
    {
        // Validate received data
        $errors = $this->validator->validate($data, [Validation::REQUIRE_SOLUTIONS]);
        if (count($errors) > 0) {
            throw new ValidationException('Category is not valid', $errors);
        }

        // Update Category with new data
        $this->serializer->deserialize($data, $category);

        // Save to DB
        $this->om->persist($category);
        $this->om->flush();

        return $category;
    }

    /**
     * Serializes a Category.
     *
     * @param Category $category
     * @param array    $options
     *
     * @return \stdClass
     */
    public function serialize(Category $category, array $options = [])
    {
        return $this->serializer->serialize($category, $options);
    }

    /**
     * Deletes a Category.
     *
     * @param Category $category
     *
     * @throws ValidationException
     */
    public function delete(Category $category)
    {
        $count = $this->repository->countQuestions($category);
        if ($count > 0) {
            throw new ValidationException('Category can not be deleted', [[
                'path' => '',
                'message' => "category is used by {$count} questions",
            ]]);
        }

        $this->om->remove($category);
        $this->om->flush();
    }
}
