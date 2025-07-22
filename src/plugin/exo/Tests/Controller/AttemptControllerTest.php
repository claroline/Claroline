<?php

namespace UJM\ExoBundle\Tests\Controller;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\RequestTrait;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Hint;
use UJM\ExoBundle\Library\Attempt\PaperGenerator;
use UJM\ExoBundle\Library\Testing\Persister;
use UJM\ExoBundle\Manager\AttemptManager;

/**
 * Tests that are common to all exercise / question types.
 */
class AttemptControllerTest extends TransactionalTestCase
{
    use RequestTrait;

    private ?ObjectManager $om;
    private ?PaperGenerator $paperGenerator;
    private ?Persister $persist;
    private ?AttemptManager $attemptManager;
    private ?User $john;
    private ?User $bob;
    private ?Hint $hi1;
    private ?Exercise $ex1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->om = $this->client->getContainer()->get('Claroline\AppBundle\Persistence\ObjectManager');
        $this->paperGenerator = $this->client->getContainer()->get('ujm_exo.generator.paper');
        $this->attemptManager = $this->client->getContainer()->get('UJM\ExoBundle\Manager\AttemptManager');

        $this->persist = $this->client->getContainer()->get(Persister::class);
        $this->john = $this->persist->user('john');
        $this->bob = $this->persist->user('bob');

        $ch1 = $this->persist->qcmChoice('ch1', 1, 1);
        $ch2 = $this->persist->qcmChoice('ch2', 2, 0);
        $qu1 = $this->persist->choiceQuestion('qu1', [$ch1, $ch2]);
        $this->hi1 = $this->persist->hint($qu1, 'hi1');
        $this->ex1 = $this->persist->exercise('ex1', [$qu1], $this->john);

        // Set up Exercise permissions
        // create 'open' mask in db
        $type = $this->ex1->getResourceNode()->getResourceType();
        $this->persist->maskDecoder($type, 'open', 1);
        $this->om->flush();

        $rightsManager = $this->client->getContainer()->get('claroline.manager.rights_manager');
        $roleManager = $this->client->getContainer()->get('claroline.manager.role_manager');

        // add open permissions to all users
        $rightsManager->update(1, $roleManager->getRoleByName('ROLE_USER'), $this->ex1->getResourceNode());

        $this->om->flush();
    }

    public function testAnonymousAttempt(): void
    {
        $this->request('POST', "/exercises/{$this->ex1->getUuid()}/attempts");
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testNewAttempt(): void
    {
        $this->request('POST', "/exercises/{$this->ex1->getUuid()}/attempts", $this->john);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($content);
        $this->assertTrue(property_exists($content, 'id'));
        $this->assertTrue(property_exists($content, 'structure'));
    }

    public function testContinueAttempt(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Checks that a basic user (i.e., not admin of the resource)
     * Cannot make a new attempt if max attempts is reached.
     */
    public function testAttemptMaxAttemptsReached(): void
    {
        // set exercise max attempts
        $this->ex1->setMaxAttempts(1);
        $this->om->persist($this->ex1);

        // first attempt for bob
        $paper = $this->paperGenerator->create($this->ex1, $this->bob);
        $this->om->persist($paper);

        $this->om->flush();

        // finish bob's first paper
        $this->attemptManager->end($paper);

        // second attempt for bob
        $this->request('POST', "/exercises/{$this->ex1->getUuid()}/attempts", $this->bob);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Checks that an admin user (i.e., admin of the resource)
     * Can make a new attempt even if max attempts is reached.
     */
    public function testAttemptMaxAttemptsReachedAdmin(): void
    {
        // set exercise max attempts
        $this->ex1->setMaxAttempts(1);
        $this->om->persist($this->ex1);

        // first attempt for bob
        $paper = $this->paperGenerator->create($this->ex1, $this->john);
        $this->om->persist($paper);
        $this->om->flush();

        // finish john's first paper
        $this->attemptManager->end($paper);
        $this->om->flush();

        // second attempt for john
        $this->request('POST', "/exercises/{$this->ex1->getUuid()}/attempts", $this->john);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($content);
    }

    public function testAnonymousSubmit(): void
    {
        $pa1 = $this->paperGenerator->create($this->ex1, $this->john);
        $this->om->persist($pa1);
        $this->om->flush();

        $this->request('PUT', "/exercises/{$this->ex1->getUuid()}/attempts/{$pa1->getUuid()}");
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testSubmitAfterPaperEnd(): void
    {
        $pa1 = $this->paperGenerator->create($this->ex1, $this->john);
        $date = new \DateTime();
        $date->add(\DateInterval::createFromDateString('yesterday'));
        $pa1->setEnd($date);

        $this->om->persist($pa1);
        $this->om->flush();

        $this->request('PUT', "/exercises/{$this->ex1->getUuid()}/attempts/{$pa1->getUuid()}", $this->john);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testSubmitByNotPaperUser(): void
    {
        $pa1 = $this->paperGenerator->create($this->ex1, $this->john);
        $this->om->persist($pa1);
        $this->om->flush();

        $this->request('PUT', "/exercises/{$this->ex1->getUuid()}/attempts/{$pa1->getUuid()}", $this->bob);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testSubmit()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet. This needs to use a data provider to submit answers of all types.'
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testSubmitInvalidData(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet. This needs to use a data provider to submit answers of all types.'
        );

        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue(is_array($content));
        $this->assertTrue(count($content) > 0);
    }

    public function testFinishPaperByNotPaperUser(): void
    {
        $pa1 = $this->paperGenerator->create($this->ex1, $this->john);
        $this->om->persist($pa1);
        $this->om->flush();

        $this->request('PUT', "/exercises/{$this->ex1->getUuid()}/attempts/{$pa1->getUuid()}/end", $this->bob);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testFinishPaper(): void
    {
        $pa1 = $this->paperGenerator->create($this->ex1, $this->john);
        $this->om->persist($pa1);
        $this->om->flush();

        // end the paper
        $this->request('PUT', "/exercises/{$this->ex1->getUuid()}/attempts/{$pa1->getUuid()}/end", $this->john);

        // Check if the Paper has been correctly updated
        $this->assertFalse($pa1->isInterrupted());
        $this->assertTrue(null !== $pa1->getEnd());

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // Check the paper is correctly returned to User
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($content);
    }

    public function testHint(): void
    {
        $pa1 = $this->paperGenerator->create($this->ex1, $this->john);

        $this->om->persist($pa1);
        $this->om->flush();
        $this->request(
            'GET',
            "/exercises/{$this->ex1->getUuid()}/attempts/{$pa1->getUuid()}/{$this->hi1->getQuestion()->getUuid()}/hints/{$this->hi1->getUuid()}",
            $this->john
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $hintData = json_decode($this->client->getResponse()->getContent());

        $this->assertInstanceOf('\stdClass', $hintData);
        $this->assertEquals('hi1', $hintData->value);
    }

    public function testAnonymousHint(): void
    {
        $pa1 = $this->paperGenerator->create($this->ex1, $this->john);
        $this->om->persist($pa1);
        $this->om->flush();

        $this->request('GET', "/exercises/{$this->ex1->getUuid()}/attempts/{$pa1->getUuid()}/{$this->hi1->getQuestion()->getUuid()}/hints/{$this->hi1->getUuid()}");
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testHintAfterPaperEnd(): void
    {
        $pa1 = $this->paperGenerator->create($this->ex1, $this->john);
        $date = new \DateTime();
        $date->add(\DateInterval::createFromDateString('yesterday'));
        $pa1->setEnd($date);

        $this->om->persist($pa1);
        $this->om->flush();

        $this->request('GET', "/exercises/{$this->ex1->getUuid()}/attempts/{$pa1->getUuid()}/{$this->hi1->getQuestion()->getUuid()}/hints/{$this->hi1->getUuid()}", $this->john);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testHintByNotPaperUser(): void
    {
        $pa1 = $this->paperGenerator->create($this->ex1, $this->john);
        $this->om->persist($pa1);
        $this->om->flush();

        $this->request('GET', "/exercises/{$this->ex1->getUuid()}/attempts/{$pa1->getUuid()}/{$this->hi1->getQuestion()->getUuid()}/hints/{$this->hi1->getUuid()}", $this->bob);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testHintNotRelatedToPaper(): void
    {
        // Create an hint not linked to paper
        $hint = $this->persist->hint($this->persist->openQuestion('question'), 'hint 2');

        $pa1 = $this->paperGenerator->create($this->ex1, $this->john);

        $this->om->persist($pa1);
        $this->om->flush();

        $this->request('GET', "/exercises/{$this->ex1->getUuid()}/attempts/{$pa1->getUuid()}/{$hint->getQuestion()->getUuid()}/hints/{$hint->getUuid()}", $this->john);

        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());

        // Checks we get errors
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue(is_array($content));
        $this->assertTrue(count($content) > 0);
    }

    public function testHintNotRelatedToQuestion(): void
    {
        // Add a new question in the exercise
        $question = $this->persist->openQuestion('open');
        $this->ex1->getSteps()->get(0)->addQuestion($question);

        $pa1 = $this->paperGenerator->create($this->ex1, $this->john);
        $this->om->persist($pa1);

        $this->om->flush();

        $this->request(
            'GET',
            "/exercises/{$this->ex1->getUuid()}/attempts/{$pa1->getUuid()}/{$question->getUuid()}/hints/{$this->hi1->getUuid()}",
            $this->john
        );

        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());

        // Checks we get errors
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue(is_array($content));
        $this->assertTrue(count($content) > 0);
    }
}
