<?php

namespace UJM\ExoBundle\Tests\Controller\Api;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\RequestTrait;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Library\Attempt\PaperGenerator;
use UJM\ExoBundle\Library\Testing\Persister;

class CorrectionControllerTest extends TransactionalTestCase
{
    use RequestTrait;

    /** @var ObjectManager */
    private $om;
    /** @var Persister */
    private $persist;
    /** @var PaperGenerator */
    private $paperGenerator;

    /** @var User */
    private $bob;
    /** @var User */
    private $admin;
    /** @var Exercise */
    private $exercise;

    protected function setUp()
    {
        parent::setUp();

        $this->om = $this->client->getContainer()->get('claroline.persistence.object_manager');
        $this->paperGenerator = $this->client->getContainer()->get('ujm_exo.generator.paper');
        $this->persist = new Persister($this->om);

        $this->bob = $this->persist->user('bob');

        $adminRole = $this->persist->role('ROLE_ADMIN');
        $this->admin = $this->persist->user('admin');
        $this->admin->addRole($adminRole);

        $this->exercise = $this->persist->exercise('ex1', [
            $this->persist->openQuestion('Question.'),
            $this->persist->openQuestion('Question 2.'),
        ], $this->admin);

        // Set up Exercise permissions
        // create 'open' mask in db
        $type = $this->exercise->getResourceNode()->getResourceType();
        $this->persist->maskDecoder($type, 'open', 1);
        $this->om->flush();

        $rightsManager = $this->client->getContainer()->get('claroline.manager.rights_manager');
        $roleManager = $this->client->getContainer()->get('claroline.manager.role_manager');

        // add open permissions to all users
        $rightsManager->editPerms(1, $roleManager->getRoleByName('ROLE_USER'), $this->exercise->getResourceNode());

        $this->om->flush();
    }

    public function testNonAdminListQuestions()
    {
        $this->request('GET', "/api/exercises/{$this->exercise->getUuid()}/correction", $this->bob);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testListQuestions()
    {
        /** @var Item $firstQuestion */
        $firstQuestion = $this->exercise->getSteps()->get(0)->getStepQuestions()->get(0)->getQuestion();
        /** @var Item $secondQuestion */
        $secondQuestion = $this->exercise->getSteps()->get(1)->getStepQuestions()->get(0)->getQuestion();

        $paper = $this->paperGenerator->create($this->exercise, $this->bob);
        // Set paper end because we can not correct unfinished papers
        $paper->setEnd(new \DateTime());

        // Create a question that needs to be corrected
        $answerToCorrect = new Answer();
        $answerToCorrect->setQuestionId($firstQuestion->getUuid());
        $answerToCorrect->setIp('127.0.0.1');
        $paper->addAnswer($answerToCorrect);

        // Create a corrected answer (should not be returned by api)
        $answer = new Answer();
        $answer->setQuestionId($secondQuestion->getUuid());
        $answer->setIp('127.0.0.1');
        $answer->setScore(5);
        $paper->addAnswer($answer);

        $this->om->persist($paper);
        $this->om->flush();

        $this->request('GET', "/api/exercises/{$this->exercise->getUuid()}/correction", $this->admin);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertInternalType('object', $content);

        // Checks returned questions
        $this->assertTrue(property_exists($content, 'questions'));
        $this->assertTrue(is_array($content->questions));
        $this->assertCount(1, $content->questions);
        $this->assertEquals($firstQuestion->getUuid(), $content->questions[0]->id);

        // Checks returned answers
        $this->assertTrue(property_exists($content, 'answers'));
        $this->assertTrue(is_array($content->answers));
        $this->assertCount(1, $content->answers);
        $this->assertEquals($answerToCorrect->getUuid(), $content->answers[0]->id);
    }

    public function testNonAdminSave()
    {
        /** @var Item $question */
        $question = $this->exercise->getSteps()->get(0)->getStepQuestions()->get(0)->getQuestion();

        $this->request('PUT', "/api/exercises/{$this->exercise->getUuid()}/correction/{$question->getUuid()}", $this->bob);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testSaveInvalidData()
    {
        /** @var Item $question */
        $question = $this->exercise->getSteps()->get(1)->getStepQuestions()->get(0)->getQuestion();

        // Don't send anything to the server, this will throw a validation error
        $this->request('PUT', "/api/exercises/{$this->exercise->getUuid()}/correction/{$question->getUuid()}", $this->admin);

        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());
    }

    public function testSaveUnknownAnswer()
    {
        /** @var Item $question */
        $question = $this->exercise->getSteps()->get(0)->getStepQuestions()->get(0)->getQuestion();
        $answerData = [[
            'id' => uniqid(), // Unknown answer
            'questionId' => uniqid(),
            'score' => 10,
            'feedback' => 'this is a feedback',
        ]];

        $this->request(
            'PUT',
            "/api/exercises/{$this->exercise->getUuid()}/correction/{$question->getUuid()}",
            $this->admin,
            [],
            json_encode($answerData)
        );
        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue(is_array($content));
        $this->assertTrue(count($content) > 0);
    }

    public function testSave()
    {
        /** @var Item $question */
        $question = $this->exercise->getSteps()->get(0)->getStepQuestions()->get(0)->getQuestion();

        $paper = $this->paperGenerator->create($this->exercise, $this->bob);
        $answerToCorrect = new Answer();
        $answerToCorrect->setQuestionId($question->getUuid());
        $answerToCorrect->setIp('127.0.0.1');
        $paper->addAnswer($answerToCorrect);

        $this->om->persist($paper);
        $this->om->flush();

        $answerData = [[
            'id' => $answerToCorrect->getUuid(),
            'questionId' => $question->getUuid(),
            'score' => 5,
            'feedback' => 'this is a feedback',
            'type' => 'application/x.open+json',
        ]];

        $this->request(
            'PUT',
            "/api/exercises/{$this->exercise->getUuid()}/correction/{$question->getUuid()}",
            $this->admin,
            [],
            json_encode($answerData)
        );

        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
    }
}
