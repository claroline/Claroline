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
        
        $this->em->persist($ancestor);
        $this->em->persist($firstChild);
        $this->em->persist($secondChild);
        $this->em->persist($firstDescendant);
        $this->em->persist($secondDescendant);
        
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
    
    public function testRetrievingAncestorsDoNotLoadDescendants()
    {
        
        $ancestor = new \Claroline\CommonBundle\Tests\Stub\Entity\Ancestor();
        $descendant = new \Claroline\CommonBundle\Tests\Stub\Entity\FirstDescendant();
        
        $ancestor->setAncestorField('ancestor_ancestor');
        
        $descendant->setAncestorField('ancestor_descendant');
        $descendant->setFirstChildField('child_descendant');
        $descendant->setFirstDescendantField('firstDescendant_descendant');
        
        $this->em->persist($ancestor);
        $this->em->persist($descendant);
        $this->em->flush();
        
        $allAncestorsAndDescendant = $this->em
            ->getRepository('\\Claroline\\CommonBundle\\Tests\\Stub\\Entity\\Ancestor')
            ->findAll();
        
        $allAncestors = $this->em
            ->createQuery(
                "SELECT a "
                ."FROM Claroline\CommonBundle\Tests\Stub\Entity\Ancestor a "
                ."WHERE a INSTANCE OF Claroline\CommonBundle\Tests\Stub\Entity\Ancestor"
            )
            ->getResult();
        
        
        $loadedAncestor = $allAncestors[0];
                
        $this->assertEquals(2, count($allAncestorsAndDescendant));
        $this->assertEquals(1, count($allAncestors));
        $this->assertEquals('ancestor_ancestor', $loadedAncestor->getAncestorField());
        
    }
}