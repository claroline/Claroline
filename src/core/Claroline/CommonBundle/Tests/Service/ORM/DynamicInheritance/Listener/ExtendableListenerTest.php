<?php

namespace Claroline\CommonBundle\Service\ORM\DynamicInheritance\Listener;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Events;
use Claroline\CommonBundle\Tests\Stub\Entity\Ancestor;
use Claroline\CommonBundle\Tests\Stub\Entity\FirstChild;
use Claroline\CommonBundle\Tests\Stub\Entity\SecondChild;
use Claroline\CommonBundle\Tests\Stub\Entity\FirstDescendant;
use Claroline\CommonBundle\Tests\Stub\Entity\SecondDescendant;

class ExtendableListenerTest extends WebTestCase
{
    /** @var \Claroline\CommonBundle\Service\Testing\TransactionalTestClient */
    private $client;

    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    public function setUp()
    {
        $this->client = self::createClient();
        $this->client->beginTransaction();
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->client->beginTransaction();
    }
    
    public function tearDown()
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
        
        $firstDescendant = new FirstDescendant();
        $secondDescendant = new SecondDescendant();
        
        $firstDescendant->setAncestorField('FirstDescendant Ancestor field');
        $firstDescendant->setFirstChildField('FirstDescendant FirstChild field');
        $firstDescendant->setFirstDescendantField('FirstDescendant own field');
        $secondDescendant->setAncestorField('SecondDescendant Ancestor field');
        $secondDescendant->setFirstChildField('SecondDescendant FirstChild field');
        $secondDescendant->setSecondDescendantField('SecondDescendant own field');
        
        $this->em->persist($firstDescendant);
        $this->em->persist($secondDescendant);
        $this->em->flush();
        
        $firstLoaded = $this->em->createQueryBuilder()
            ->select('f')
            ->from('Claroline\CommonBundle\Tests\Stub\Entity\FirstDescendant', 'f')
            ->getQuery()
            ->getSingleResult();
        
        $secondLoaded = $this->em->createQueryBuilder()
            ->select('s')
            ->from('Claroline\CommonBundle\Tests\Stub\Entity\SecondDescendant', 's')
            ->getQuery()
            ->getSingleResult();
        
        $this->assertEquals('FirstDescendant Ancestor field', $firstLoaded->getAncestorField());
        $this->assertEquals('SecondDescendant Ancestor field', $secondLoaded->getAncestorField());
        $this->assertNotEquals($firstLoaded->getId(), $secondLoaded->getId());        
    }
    
    public function testRetrievingAncestorsDoNotLoadDescendants()
    {   
        $ancestor = new Ancestor();
        $descendant = new FirstDescendant();
        
        $ancestor->setAncestorField('Ancestor own field');
        
        $descendant->setAncestorField('FirstDescendant Ancestor field');
        $descendant->setFirstChildField('FirstDescendant FirstChild field');
        $descendant->setFirstDescendantField('FirstDescendant own field');
        
        $this->em->persist($ancestor);
        $this->em->persist($descendant);
        $this->em->flush();
        
        $allAncestorsAndDescendant = $this->em
            ->getRepository('Claroline\CommonBundle\Tests\Stub\Entity\Ancestor')
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
        $this->assertEquals('Ancestor own field', $loadedAncestor->getAncestorField());
    }
}