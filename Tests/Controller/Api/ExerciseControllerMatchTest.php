<?php

namespace UJM\ExoBundle\Tests\Controller\Api;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Persistence\ObjectManager;
use UJM\ExoBundle\Entity\Choice;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Hint;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Testing\Persister;

/**
 * Tests that are specific to MatchQuestionType
 */
class ExerciseControllerMatchTest extends TransactionalTestCase
{
    /** @var ObjectManager */
    private $om;
    /** @var Persister */
    private $persist;
    /** @var User */
    private $john;
    /** @var User */
    private $bob;
    /** @var User */
    private $admin;
    /** @var Label */
    private $lab1;
    /** @var Label */
    private $lab2;
    /** @var Proposal */
    private $prop1;
    /** @var Proposal */
    private $prop2;
     /** @var Proposal */
    private $prop3;
    /** @var Question */
    private $qu1;
    /** @var Exercise */
    private $ex1;
   

    protected function setUp()
    {
        parent::setUp();
        $this->om = $this->client->getContainer()->get('claroline.persistence.object_manager');
        $manager = $this->client->getContainer()->get('ujm.exo.paper_manager');
        $this->persist = new Persister($this->om, $manager);
        $this->john = $this->persist->user('john');
        $this->bob = $this->persist->user('bob');
        
        $this->persist->role('ROLE_ADMIN');
        $this->admin = $this->persist->user('admin');
        
        // real label that will be associated with proposals
        $this->lab1 = $this->persist->matchLabel('fruit', 2);
        // orphan label that will have 0 associated proposal
        $this->lab2 = $this->persist->matchLabel('vegetable');
        
        $this->prop1 = $this->persist->matchProposal('peach', $this->lab1);
        $this->prop2 = $this->persist->matchProposal('apple', $this->lab1);
        // proposal without any associated label
        $this->prop3 = $this->persist->matchProposal('duck');

        $this->qu1 = $this->persist->matchQuestion('match1', [$this->lab1, $this->lab2], [$this->prop1, $this->prop2, $this->prop3]);       
        $this->ex1 = $this->persist->exercise('ex1', [$this->qu1], $this->john);
        $this->om->flush();
    }   
    
    public function testSubmitAnswerInInvalidFormat()
    {
        $pa1 = $this->persist->paper($this->john, $this->ex1);
        $this->om->flush();

        $this->request(
            'PUT',
            "/exercise/api/papers/{$pa1->getId()}/questions/{$this->qu1->getId()}",
            $this->john,
            ['data' => ['not a proposal id,not a label id']]
        );
        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());
    }

    public function testSubmitAnswer()
    {
        $pa1 = $this->persist->paper($this->john, $this->ex1);
        $this->om->flush();
        
        $propId1 = (string) $this->prop1->getId();
        $propId2 = (string) $this->prop2->getId();
        $labelId = (string) $this->lab1->getId();          
                
        $this->request(
            'PUT',
            "/exercise/api/papers/{$pa1->getId()}/questions/{$this->qu1->getId()}",
            $this->john,
            ['data' => [$propId1.','.$labelId, $propId2.','.$labelId]]
        );
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
    }

    private function request($method, $uri, User $user = null, array $parameters = [])
    {
        $server = $user ?
            [
                'PHP_AUTH_USER' => $user->getUsername(),
                'PHP_AUTH_PW' => $this->john->getPlainPassword()
            ] :
            [];

        return $this->client->request($method, $uri, $parameters, [], $server);
    }
}
