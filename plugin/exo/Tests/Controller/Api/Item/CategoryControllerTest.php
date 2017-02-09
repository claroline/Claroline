<?php

namespace UJM\ExoBundle\Tests\Controller\Api\Item;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\RequestTrait;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Persistence\ObjectManager;
use UJM\ExoBundle\Entity\Item\Category;
use UJM\ExoBundle\Library\Testing\Persister;

class CategoryControllerTest extends TransactionalTestCase
{
    use RequestTrait;

    /**
     * @var Persister
     */
    private $persist;

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var User
     */
    private $john;

    /**
     * @var User
     */
    private $bob;

    /**
     * @var Category
     */
    private $categoryJohn;

    /**
     * @var Category
     */
    private $categoryBob;

    protected function setUp()
    {
        parent::setUp();

        $this->om = $this->client->getContainer()->get('claroline.persistence.object_manager');
        $this->persist = new Persister($this->om);

        // Initialize some data
        $this->john = $this->persist->user('john');
        $this->bob = $this->persist->user('bob');
        $this->categoryJohn = $this->persist->category('categoryJohn', $this->john);
        $this->categoryBob = $this->persist->category('categoryBob', $this->bob);

        $this->om->flush();
    }

    /**
     * The `list` action MUST return a 200 status code.
     * The `list` action MUST return an array.
     * The `list` action MUST only return the categories of the current user.
     */
    public function testListUserCategories()
    {
        $this->request('GET', '/api/categories', $this->john);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertTrue(is_array($content));

        // Only john's categories
        $this->assertEquals(1, count($content));
    }

    /**
     * The `create` action MUST return a 201 status code when receiving valid data.
     * The `create` action MUST return an object representing the created category.
     */
    public function testCreateCategory()
    {
        $newData = [
            'id' => uniqid(),
            'name' => 'categoryCreate',
        ];

        $this->request(
            'POST',
            '/api/categories',
            $this->john,
            [],
            json_encode($newData)
        );

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertInstanceOf('\stdClass', $content);
        $this->assertEquals($newData['id'], $content->id);
        $this->assertEquals($newData['name'], $content->name);

        // Checks that John has one more category
        // John has 2 categories : the one created in the `setUp` + this one
        $categories = $this->om->getRepository('UJMExoBundle:Item\Category')->findBy([
            'user' => $this->john,
        ]);
        $this->assertCount(2, $categories);
    }

    /**
     * The `create` action MUST return a 422 status code when receiving invalid data.
     * The `create` action MUST return the list of validation errors.
     */
    public function testCreateCategoryWithInvalidData()
    {
        $invalidData = [
            'id' => uniqid(),
            'name' => ['not-a-string'],
        ];

        $this->request(
            'POST',
            '/api/categories',
            $this->john,
            [],
            json_encode($invalidData)
        );

        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue(is_array($content));
        $this->assertTrue(count($content) > 0);

        $this->assertContains([
            'path' => '/name',
            'message' => 'instance must be of type string',
        ], $content);
    }

    /**
     * The `create` action MUST return a 200 status code
     * The `create` action MUST return an object representing the updated category.
     */
    public function testUpdateCategory()
    {
        $updateData = [
            'id' => $this->categoryJohn->getUuid(),
            'name' => 'categoryUpdate',
        ];

        $this->request(
            'PUT',
            "/api/categories/{$this->categoryJohn->getUuid()}",
            $this->john,
            [],
            json_encode($updateData)
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent());

        $this->assertInstanceOf('\stdClass', $content);
        $this->assertEquals($updateData['name'], $content->name);
    }

    /**
     * The `update` action MUST return a 422 status code
     * The `update` action MUST return the list of validation errors.
     */
    public function testUpdateCategoryWithInvalidData()
    {
        $invalidData = [
            'id' => $this->categoryJohn->getUuid(),
            'name' => ['not-a-string'],
        ];

        $this->request(
            'PUT',
            "/api/categories/{$this->categoryJohn->getUuid()}",
            $this->john,
            [],
            json_encode($invalidData)
        );

        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue(is_array($content));
        $this->assertTrue(count($content) > 0);

        $this->assertContains([
            'path' => '/name',
            'message' => 'instance must be of type string',
        ], $content);
    }

    /**
     * The `update` action MUST NOT allow to update a category by a user not linked to the category.
     */
    public function testUpdateCategoryByNotAdminUser()
    {
        $updateData = [
            'id' => $this->categoryBob->getUuid(),
            'name' => 'categoryUpdate',
        ];

        $this->request(
            'PUT',
            "/api/categories/{$this->categoryBob->getUuid()}",
            $this->john,
            [],
            json_encode($updateData)
        );

        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * The `delete` action MUST return a 204 status code.
     * The `delete` action MUST return null.
     */
    public function testDeleteCategory()
    {
        $this->request('DELETE', "/api/categories/{$this->categoryJohn->getUuid()}", $this->john);

        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(null, $this->client->getResponse()->getContent());
    }

    /**
     * The `delete` action MUST NOT allow to delete a category by a user not linked to the category.
     */
    public function testDeleteCategoryByNotAdminUser()
    {
        $this->request('DELETE', "/api/categories/{$this->categoryBob->getUuid()}", $this->john);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * The `delete` action MUST NOT allow to delete a category which contains questions.
     */
    public function testDeleteCategoryWithQuestions()
    {
        // Adds a question to the category
        $question = $this->persist->openQuestion('Open question');
        $question->setCategory($this->categoryJohn);
        $this->om->flush();

        $this->request('DELETE', "/api/categories/{$this->categoryJohn->getUuid()}", $this->john);

        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue(is_array($content));
        $this->assertTrue(count($content) > 0);
        $this->assertContains([
            'path' => '',
            'message' => 'category is used by 1 questions',
        ], $content);
    }
}
