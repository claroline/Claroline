<?php

namespace Claroline\CommonBundle\Service\ORM\DynamicInheritance\Listener;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Events;

class ExtendableListenerTest extends WebTestCase
{
    /** @var \Claroline\CommonBundle\Service\Testing\TransactionalTestClient */
    private $client;

    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    public function setUp()
    {
        parent::setUp();
        $this->client = self::createClient();
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }
    
    public function testExtendableListenerIsSubscribed()
    {
        $listeners = $this->em->getEventManager()->getListeners(Events::loadClassMetadata);
        
        foreach ($listeners as $listener)
        {
            if ($listener instanceof ExtendableListener)
            {
                return;
            }
        }
        
        $this->fail('The ExtendableListener is not attached to the default EntityManager.');
    }
    
    public function testEntityManagerCanLoadStubEntitiesWithDynamicMapping()
    {
        $ancestor = new \Claroline\CommonBundle\Tests\Stub\Entity\Ancestor();
        $firstChild = new \Claroline\CommonBundle\Tests\Stub\Entity\FirstChild();
        $secondChild = new \Claroline\CommonBundle\Tests\Stub\Entity\SecondChild();
        $firstDescendant = new \Claroline\CommonBundle\Tests\Stub\Entity\FirstDescendant();
        $secondDescendant = new \Claroline\CommonBundle\Tests\Stub\Entity\SecondDescendant();
        
        $this->em->persist($ancestor);
        $this->em->persist($firstChild);
        $this->em->persist($secondChild);
        $this->em->persist($firstDescendant);
        $this->em->persist($secondDescendant);
    }
}