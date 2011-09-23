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
        $this->client->beginTransaction();
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }
    
    protected function tearDown()
    {
        $this->client->rollback();
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
        
    }
    
    public function testEntityManagerCanPersistAndFindEntitiesWithDynamicMapping()
    {
        
        $firstDescendant = new \Claroline\CommonBundle\Tests\Stub\Entity\FirstDescendant();
        $secondDescendant = new \Claroline\CommonBundle\Tests\Stub\Entity\SecondDescendant();
        
        $firstDescendant->setAncestorField('ancestor_first');
        $firstDescendant->setFirstChildField('first_child_first');
        $firstDescendant->setFirstDescendantField('firstDescendant_first');
        $secondDescendant->setAncestorField('ancestor_second');
        $secondDescendant->setFirstChildField('first_child_second');
        $secondDescendant->setSecondDescendantField('secondDescendant_second');
        
        $this->em->persist($firstDescendant);
        $this->em->persist($secondDescendant);
        $this->em->flush();
        
        $firstLoaded = $this->em->createQueryBuilder()
            ->select('f')
            ->from('\Claroline\CommonBundle\Tests\Stub\Entity\FirstDescendant', 'f')
            ->getQuery()
            ->getSingleResult();
        
        
        $secondLoaded = $this->em->createQueryBuilder()
            ->select('s')
            ->from('\Claroline\CommonBundle\Tests\Stub\Entity\SecondDescendant', 's')
            ->getQuery()
            ->getSingleResult();
        
        $this->assertEquals('ancestor_first', $firstLoaded->getAncestorField());
        $this->assertEquals('ancestor_second', $secondLoaded->getAncestorField());
        $this->assertNotEquals($firstLoaded->getId(), $secondLoaded->getId());
        
    }
}