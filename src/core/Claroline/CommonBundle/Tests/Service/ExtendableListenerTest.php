<?php

namespace Claroline\CommonBundle\Service\ORM;

use Doctrine\ORM\Events;
use Claroline\CommonBundle\Library\Testing\TransactionalTestCase;
use Claroline\CommonBundle\Tests\Stub\Entity\ValidHierarchy\Ancestor;
use Claroline\CommonBundle\Tests\Stub\Entity\ValidHierarchy\FirstChild;
use Claroline\CommonBundle\Tests\Stub\Entity\ValidHierarchy\SecondChild;
use Claroline\CommonBundle\Tests\Stub\Entity\ValidHierarchy\FirstDescendant;
use Claroline\CommonBundle\Tests\Stub\Entity\ValidHierarchy\SecondDescendant;
use Claroline\CommonBundle\Tests\Stub\Entity\NodeHierarchy\TreeAncestor;
use Claroline\CommonBundle\Tests\Stub\Entity\NodeHierarchy\FirstChild as TreeFirstChild;
use Claroline\CommonBundle\Tests\Stub\Entity\NodeHierarchy\SecondChild as TreeSecondChild;

class ExtendableListenerTest extends TransactionalTestCase
{
    /** Doctrine\ORM\EntityManager */
    private $em;

    public function setUp()
    {
        parent :: setUp();
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

    /**
     * @dataProvider conflictualMappingEntityProvider
     */
    public function testLoadingAnEntityWithBothExtendableAndDoctrineInheritanceAnnotationsThrowsAnException($entityFqcn)
    {
        $this->setExpectedException('Claroline\CommonBundle\Exception\ClarolineException');
        
        $entity = new $entityFqcn();
        $this->em->persist($entity);
    }
    
    /**
     * @dataProvider invalidDiscriminatorColumnNameEntityProvider
     */
    public function testLoadingAnExtendableEntityWithInvalidDiscriminatorColumnNameThrowsAnException($entityFqcn)
    {
        $this->setExpectedException('Claroline\CommonBundle\Exception\ClarolineException');
        
        $entity = new $entityFqcn();
        $this->em->persist($entity);
    }
    
    public function testEntityManagerCanLoadEntitiesWithValidExtendableAnnotations()
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
    
    public function testEntityManagerCanPersistAndFindEntitiesThatAreChildrenOfExtendableEntities()
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
            ->from('Claroline\CommonBundle\Tests\Stub\Entity\ValidHierarchy\FirstDescendant', 'f')
            ->getQuery()
            ->getSingleResult();
        
        $secondLoaded = $this->em->createQueryBuilder()
            ->select('s')
            ->from('Claroline\CommonBundle\Tests\Stub\Entity\ValidHierarchy\SecondDescendant', 's')
            ->getQuery()
            ->getSingleResult();
        
        $this->assertEquals('FirstDescendant Ancestor field', $firstLoaded->getAncestorField());
        $this->assertEquals('SecondDescendant Ancestor field', $secondLoaded->getAncestorField());
        $this->assertNotEquals($firstLoaded->getId(), $secondLoaded->getId());
    }
    
    public function testRetrievingParentsBringsBackParentsAndChildren()
    {
        $this->flushTheWholeValidHierarchy();
        
        $ancestors = $this->em
            ->getRepository('Claroline\CommonBundle\Tests\Stub\Entity\ValidHierarchy\Ancestor')
            ->findAll();
        
        $firstChildren = $this->em
            ->getRepository('Claroline\CommonBundle\Tests\Stub\Entity\ValidHierarchy\FirstChild')
            ->findAll();
        
        $secondChildren = $this->em
            ->getRepository('Claroline\CommonBundle\Tests\Stub\Entity\ValidHierarchy\SecondChild')
            ->findAll();
        
        $this->assertEquals(5, count($ancestors));
        $this->assertEquals(3, count($firstChildren));
        $this->assertEquals(1, count($secondChildren));
    }
    
    public function testRetrievingParentsWithExactClassTypeDoesntBringBackTheirChildren()
    {
        $this->flushTheWholeValidHierarchy();
        
        $ancestors = $this->requestExactType('ValidHierarchy\Ancestor');
        $firstChildren = $this->requestExactType('ValidHierarchy\FirstChild');
        $secondChildren = $this->requestExactType('ValidHierarchy\SecondChild');
        $firstDescendants = $this->requestExactType('ValidHierarchy\FirstDescendant');
        $secondDescendants = $this->requestExactType('ValidHierarchy\SecondDescendant');
        
        $this->assertEquals(1, count($ancestors));
        $this->assertEquals(1, count($firstChildren));
        $this->assertEquals(1, count($secondChildren));
        $this->assertEquals(1, count($firstDescendants));
        $this->assertEquals(1, count($secondDescendants));
        
        $this->assertInstanceOf('Claroline\CommonBundle\Tests\Stub\Entity\ValidHierarchy\Ancestor', $ancestors[0]);
        $this->assertInstanceOf('Claroline\CommonBundle\Tests\Stub\Entity\ValidHierarchy\FirstChild', $firstChildren[0]);
        $this->assertInstanceOf('Claroline\CommonBundle\Tests\Stub\Entity\ValidHierarchy\SecondChild', $secondChildren[0]);
        $this->assertInstanceOf('Claroline\CommonBundle\Tests\Stub\Entity\ValidHierarchy\FirstDescendant', $firstDescendants[0]);
        $this->assertInstanceOf('Claroline\CommonBundle\Tests\Stub\Entity\ValidHierarchy\SecondDescendant', $secondDescendants[0]);
    }
    
    public function testDeletingAncestorsDeletesChildrenJoinedRecords()
    {
        $this->flushTheWholeValidHierarchy();
        
        $ancestors = $this->em
            ->getRepository('Claroline\CommonBundle\Tests\Stub\Entity\ValidHierarchy\Ancestor')
            ->findAll();
            
        foreach ($ancestors as $ancestor)
        {
            $this->em->remove($ancestor);
        }
        
        $this->em->flush();
        
        $connection = $this->em->getConnection();
        $firstChildren = $connection->query('SELECT * FROM claro_test_firstchild')->fetchAll();
        $secondChildren = $connection->query('SELECT * FROM claro_test_secondchild')->fetchAll();
        $firstDescendants = $connection->query('SELECT * FROM claro_test_firstdescendant')->fetchAll();
        $secondDescendants = $connection->query('SELECT * FROM claro_test_seconddescendant')->fetchAll();
        
        $this->assertEquals(0, count($firstChildren));
        $this->assertEquals(0, count($secondChildren));
        $this->assertEquals(0, count($firstDescendants));
        $this->assertEquals(0, count($firstDescendants));
        $this->assertEquals(0, count($secondDescendants));
    }
    
    public function testDeletingDescendantEntityDeletesParentsRecords()
    {
        $this->flushTheWholeValidHierarchy();
        
        $firstDescendants = $this->requestExactType('ValidHierarchy\FirstDescendant');
        
        foreach ($firstDescendants as $descendant)
        {
            $this->em->remove($descendant);
        }
        
        $this->em->flush();
        
        $connection = $this->em->getConnection();
        $ancestors = $connection->query('SELECT * FROM claro_test_ancestor')->fetchAll();
        $firstChildren = $connection->query('SELECT * FROM claro_test_firstchild')->fetchAll();
        $firstDescendants = $connection->query('SELECT * FROM claro_test_firstdescendant')->fetchAll();
        
        $this->assertEquals(4, count($ancestors));
        $this->assertEquals(2, count($firstChildren));
        $this->assertEquals(0, count($firstDescendants));
    }
    
    public function testExtendableMappingDoesntConflictWithDoctrineTreeExtension()
    {
        $this->flushTheWholeNodeHierarchy();
        
        $ancestorRepo = $this->em->getRepository('Claroline\CommonBundle\Tests\Stub\Entity\NodeHierarchy\TreeAncestor');
        
        $ancestors = $ancestorRepo->findAll();
        $rootAncestor = $ancestorRepo->findOneByTreeAncestorField('FTA');
        $rootAncestorChildNodes = $ancestorRepo->children($rootAncestor);
        $rootFirstChild = $ancestorRepo->findOneByTreeAncestorField('SFCA');
        $rootFirstChildChildNodes = $ancestorRepo->children($rootFirstChild);
        
        $this->assertEquals(5, count($ancestors));
        $this->assertEquals(3, count($rootAncestorChildNodes));
        $this->assertEquals(0, count($rootFirstChildChildNodes));
               
        $this->performRealEntityDeletion($ancestors);
    }
    
    public function testDeletingAChildNodeDeletesItsChildrenJoinedRecords()
    {
        $this->flushTheWholeNodeHierarchy();
        
        $ancestorRepo = $this->em->getRepository('Claroline\CommonBundle\Tests\Stub\Entity\NodeHierarchy\TreeAncestor');
        $ancestors = $ancestorRepo->findAll();
        $firstTreeAncestor = $ancestorRepo->findOneByTreeAncestorField('FTA');
        $firstFirstChild = $ancestorRepo->findOneByTreeAncestorField('FFCA');
        
        $this->assertEquals($firstTreeAncestor, $firstFirstChild->getParent());
        $this->assertEquals(3, count($ancestorRepo->children($firstTreeAncestor)));
        
        $this->em->remove($firstFirstChild);
        $this->em->flush();

        $this->assertEquals(2, count($ancestorRepo->children($firstTreeAncestor)));
        
        $connection = $this->em->getConnection();
        $firstChildren = $connection->query('SELECT * FROM claro_test_node_first_child')->fetchAll();
        $deletedChildren = $connection->query('SELECT * FROM claro_test_node_first_child WHERE firstChildField="FFC"')->fetchAll();
        
        $this->assertEquals(1, count($firstChildren));
        $this->assertEquals(0, count($deletedChildren));
        
        $this->performRealEntityDeletion($ancestors);
    }
    
    public function conflictualMappingEntityProvider()
    {
        return array(
            array('Claroline\CommonBundle\Tests\Stub\Entity\InvalidMapping\ConflictualMapping1'),
            array('Claroline\CommonBundle\Tests\Stub\Entity\InvalidMapping\ConflictualMapping2'),
            array('Claroline\CommonBundle\Tests\Stub\Entity\InvalidMapping\ConflictualMapping3')
        );
    }
    
    public function invalidDiscriminatorColumnNameEntityProvider()
    {
        return array(
            array('Claroline\CommonBundle\Tests\Stub\Entity\InvalidMapping\InvalidDiscriminatorColumn1'),
            array('Claroline\CommonBundle\Tests\Stub\Entity\InvalidMapping\InvalidDiscriminatorColumn2')
        );
    }
    
    private function flushTheWholeValidHierarchy()
    {
        $ancestor = new Ancestor();
        $firstChild = new FirstChild();
        $secondChild = new SecondChild();
        $firstDescendant = new FirstDescendant();
        $secondDescendant = new SecondDescendant();
        
        $ancestor->setAncestorField('A');
        $firstChild->setAncestorField('B');
        $firstChild->setFirstChildField('C');
        $secondChild->setAncestorField('D');
        $secondChild->setSecondChildField('E');
        $firstDescendant->setAncestorField('F');
        $firstDescendant->setFirstChildField('G');
        $firstDescendant->setFirstDescendantField('H');
        $secondDescendant->setAncestorField('I');
        $secondDescendant->setFirstChildField('J');
        $secondDescendant->setSecondDescendantField('K');
        
        $this->em->persist($ancestor);
        $this->em->persist($firstChild);
        $this->em->persist($secondChild);
        $this->em->persist($firstDescendant);
        $this->em->persist($secondDescendant);
        $this->em->flush();
    }
    
    private function flushTheWholeNodeHierarchy()
    {
        $firstTreeAncestor = new TreeAncestor(); // will be a root node (no parent)
        $secondTreeAncestor = new TreeAncestor();
        $firstFirstChild = new TreeFirstChild();
        $secondFirstChild = new TreeFirstChild(); // will be a root node (no parent)
        $secondChild = new TreeSecondChild();
        
        $firstTreeAncestor->setTreeAncestorField('FTA');
        $secondTreeAncestor->setTreeAncestorField('STA');
        $secondTreeAncestor->setParent($firstTreeAncestor); // child of firstTreeAncestor
        $firstFirstChild->setTreeAncestorField('FFCA');
        $firstFirstChild->setFirstChildField('FFC');
        $firstFirstChild->setParent($firstTreeAncestor); // child of firstTreeAncestor
        $secondFirstChild->setTreeAncestorField('SFCA');
        $secondFirstChild->setFirstChildField('SFC');
        $secondChild->setTreeAncestorField('SCA');
        $secondChild->setSecondChildField('SC');
        $secondChild->setParent($secondTreeAncestor); // child of second-firstTreeAncestor
        
        $this->em->persist($firstTreeAncestor);
        $this->em->persist($secondTreeAncestor);
        $this->em->persist($firstFirstChild);
        $this->em->persist($secondFirstChild);
        $this->em->persist($secondChild);
        $this->em->flush();
    }
    
    /**
     * The gedmo-doctrine tree extension alters transactional mode by creating
     * temporary tables for its calculations, and thus prevents a clean rollback
     * of the data inserted during the transaction. This method leaves the
     * transactional mode, effectively deletes the entities passed as argument
     * and opens the transaction again.
     *
     * @param array entities An array of managed entities
     */
    private function performRealEntityDeletion(array $entities)
    {
        $this->client->rollback();
        
        foreach ($entities as $entity)
        {
            $this->em->remove($entity);
        }
        
        $this->em->flush();
        $this->client->beginTransaction();
    }
    
    private function requestExactType($className)
    {
        return $this->em
            ->createQuery(
                "SELECT a "
                ."FROM Claroline\CommonBundle\Tests\Stub\Entity\\".$className." a "
                ."WHERE a INSTANCE OF Claroline\CommonBundle\Tests\Stub\Entity\\".$className
            )
            ->getResult();
    }
}