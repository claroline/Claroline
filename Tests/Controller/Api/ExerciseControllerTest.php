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

class ExerciseControllerTest extends TransactionalTestCase
{
    /** @var ObjectManager */
    private $om;
    /** @var Persister */
    private $persist;
    /** @var User */
    private $john;
    /** @var User */
    private $bob;
    /** @var Choice */
    private $ch1;
    /** @var Choice */
    private $ch2;
    /** @var Question */
    private $qu1;
    /** @var Hint */
    private $hi1;
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
        $this->ch1 = $this->persist->qcmChoice('ch1', 1);
        $this->ch2 = $this->persist->qcmChoice('ch2', 0);
        $this->qu1 = $this->persist->qcmQuestion('qu1', [$this->ch1, $this->ch2]);
        $this->hi1 = $this->persist->hint($this->qu1, 'hi1');
        $this->ex1 = $this->persist->exercise('ex1', [$this->qu1], $this->john);
        $this->om->flush();
    }

    public function testAnonymousExport()
    {
        $this->request('GET', "/exercise/api/exercises/{$this->ex1->getId()}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testNonCreatorExport()
    {
        $this->request('GET', "/exercise/api/exercises/{$this->ex1->getId()}", $this->bob);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testExport()
    {
        $this->request('GET', "/exercise/api/exercises/{$this->ex1->getId()}", $this->john);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals($this->ex1->getId(), $content->id);
        $this->assertEquals('ex1', $content->meta->title);
        $this->assertEquals('qu1', $content->steps[0]->items[0]->title);
    }

    public function testAnonymousAttempt()
    {
        $this->request('POST', "/exercise/api/exercises/{$this->ex1->getId()}/attempts");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAttempt()
    {
        $this->request('POST', "/exercise/api/exercises/{$this->ex1->getId()}/attempts", $this->john);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals($this->ex1->getId(), $content->exercise->id);
        $this->assertInternalType('object', $content->paper);
    }

    public function testAnonymousSubmit()
    {
        $pa1 = $this->persist->paper($this->john, $this->ex1);
        $this->om->flush();

        $this->request('PUT', "/exercise/api/papers/{$pa1->getId()}/questions/{$this->qu1->getId()}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testSubmitAnswerAfterPaperEnd()
    {
        $pa1 = $this->persist->paper($this->john, $this->ex1);
        $date = new \DateTime();
        $date->add(\DateInterval::createFromDateString('yesterday'));
        $pa1->setEnd($date);
        $this->om->flush();

        $this->request('PUT', "/exercise/api/papers/{$pa1->getId()}/questions/{$this->qu1->getId()}", $this->john);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testSubmitAnswerByNotPaperUser()
    {
        $pa1 = $this->persist->paper($this->john, $this->ex1);
        $this->om->flush();

        $this->request('PUT', "/exercise/api/papers/{$pa1->getId()}/questions/{$this->qu1->getId()}", $this->bob);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testSubmitAnswerInInvalidFormat()
    {
        $pa1 = $this->persist->paper($this->john, $this->ex1);
        $this->om->flush();

        $this->request(
            'PUT',
            "/exercise/api/papers/{$pa1->getId()}/questions/{$this->qu1->getId()}",
            $this->john,
            ['not a choice id']
        );
        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());
    }

    public function testSubmitAnswer()
    {
        $pa1 = $this->persist->paper($this->john, $this->ex1);
        $this->om->flush();

        $this->request(
            'PUT',
            "/exercise/api/papers/{$pa1->getId()}/questions/{$this->qu1->getId()}",
            $this->john,
            [(string) $this->ch1->getId()]
        );
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
    }

    public function testAnonymousHint()
    {
        $pa1 = $this->persist->paper($this->john, $this->ex1);
        $this->om->flush();

        $this->request('GET', "/exercise/api/papers/{$pa1->getId()}/hints/{$this->hi1->getId()}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testHintAfterPaperEnd()
    {
        $pa1 = $this->persist->paper($this->john, $this->ex1);
        $date = new \DateTime();
        $date->add(\DateInterval::createFromDateString('yesterday'));
        $pa1->setEnd($date);
        $this->om->flush();

        $this->request('GET', "/exercise/api/papers/{$pa1->getId()}/hints/{$this->hi1->getId()}", $this->john);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testHintByNotPaperUser()
    {
        $pa1 = $this->persist->paper($this->john, $this->ex1);
        $this->om->flush();

        $this->request('GET', "/exercise/api/papers/{$pa1->getId()}/hints/{$this->hi1->getId()}", $this->bob);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testHint()
    {
        $pa1 = $this->persist->paper($this->john, $this->ex1);
        $this->om->flush();

        $this->request('GET', "/exercise/api/papers/{$pa1->getId()}/hints/{$this->hi1->getId()}", $this->john);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('hi1', json_decode($this->client->getResponse()->getContent()));
    }

    public function testFinishPaperByNotPaperCreator()
    {
        $pa1 = $this->persist->paper($this->john, $this->ex1);
        $this->om->flush();

        $this->request('PUT', "/exercise/api/papers/{$pa1->getId()}/end", $this->bob);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testFinishPaper()
    {
        $pa1 = $this->persist->paper($this->john, $this->ex1);
        $this->om->flush();

        $this->request('PUT', "/exercise/api/papers/{$pa1->getId()}/end", $this->john);
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
    }

    public function testAnonymousPapers()
    {
        $this->request('GET', "/exercise/api/exercises/{$this->ex1->getId()}/papers");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testUserPapers()
    {
        $pa1 = $this->persist->paper($this->john, $this->ex1);
        $this->om->flush();

        $this->request('GET', "/exercise/api/exercises/{$this->ex1->getId()}/papers", $this->john);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(1, count($content->questions));
        $this->assertEquals($this->qu1->getId(), $content->questions[0]->id);
        $this->assertEquals(1, count($content->papers));
        $this->assertEquals($pa1->getId(), $content->papers[0]->id);
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
