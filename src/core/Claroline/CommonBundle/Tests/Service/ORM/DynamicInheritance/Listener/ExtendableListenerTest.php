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
        $ancestor = new Ancestor();
        $firstChild = new FirstChild();
        $secondChild = new SecondChild();
        $firstDescendant = new FirstDescendant();
        $secondDescendant = new SecondDescendant();
        
        $this->em->persist($ancestor);
        $this->em->persist($firstChild);
        $this->em->persist($secondChild);
        $this->em->persist($firstDescendant);
        $this->em->persist($secondDescendant);
    }
    
    public function testHierarchyCanBeFlushedAndRetrievedAsExpected()
    {
        $ancestor = new Ancestor();
        $ancestor->setAncestorField('Ancestor');
        $firstChild = new FirstChild();
        $firstChild->setAncestorField('FirstChild Ancestor field');
        $firstChild->setFirstChildField('FirstChild own field');
        $secondChild = new SecondChild();
        $secondChild->setAncestorField('SecondChild Ancestor field');
        $secondChild->setSecondChildField('SecondChild own field');
        $firstDescendant = new FirstDescendant();
        $firstDescendant->setAncestorField('FirstDescendant Ancestor field');
        $firstDescendant->setFirstChildField('FirstDescendant FirstChield field');
        $firstDescendant->setFirstDescendantField('FirstDescendant own field');
        $secondDescendant = new SecondDescendant();
        $secondDescendant->setAncestorField('SecondDescendant');
        $secondDescendant->setFirstChildField('SecondDescendant FirstChild field');
        $secondDescendant->setSecondDescendantField('SecondDescendant own field');
        
        $this->em->persist($ancestor);
        $this->em->persist($firstChild);
        $this->em->persist($secondChild);
        $this->em->persist($firstDescendant);
        $this->em->persist($secondDescendant);
        
        $this->em->flush();
        
        $stubEntities = $this->em
            ->getRepository('Claroline\\CommonBundle\\Tests\\Stub\\Entity\\Ancestor')
            ->findAll();
        
        $this->assertEquals(5, count($stubEntities));
    }
}