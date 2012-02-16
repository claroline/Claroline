<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Annotations\FileCacheReader;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Claroline\CoreBundle\Exception\ClarolineException;
use Claroline\CoreBundle\Annotation\ORM\Extendable;

class ExtendableListener extends ContainerAware implements EventSubscriber
{
    // metadatas of the @extendable entities
    private $extendables = array();
    
    public function getSubscribedEvents() 
    {
        return array(Events::loadClassMetadata);  
    }
    
    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        $classMetadata = $event->getClassMetadata();
        $className = $classMetadata->getName();
        $classDiscriminator = $classMetadata->getTableName();
        
        $extendableAnnotation = $this->getExtendableAnnotation($className);
        
        if ($extendableAnnotation !== false)
        {
            // the entity is @extendable
            $discriminatorColumn = (string) $extendableAnnotation->discriminatorColumn;
            $classMetadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_JOINED);
            $classMetadata->setDiscriminatorColumn(
                array(
                    'name' => $discriminatorColumn, 
                    'type' => 'string',
                    'length' => 255
                )
            );
            $classMetadata->setDiscriminatorMap(array($classDiscriminator => $className));
            $this->extendables[$className] = $classMetadata;
        }
        
        foreach ($this->extendables as $extendableClassMetadata)
        {
            if (is_subclass_of($className, $extendableClassMetadata->getName()))
            {
                // the entity is a child of an @extendable entity
                $classMetadata->discriminatorValue = $classDiscriminator;           
                $classMetadata->discriminatorMap[$classDiscriminator] = $className; 
                $extendableClassMetadata->discriminatorMap[$classDiscriminator] = $className;
                $extendableClassMetadata->subClasses[] = $className;
            }
        }
    }
    
    private function getExtendableAnnotation($className)
    {
        $reflectionClass = new \ReflectionClass($className);
        $annotations = $this->getReader()->getClassAnnotations($reflectionClass);
        $hasDoctrineInheritanceMapping = false;
        $extendableAnnotation = false;
        
        foreach ($annotations as $annotation)
        {            
            if ($annotation instanceof Extendable)
            {
                $this->checkExtendableAnnotation($annotation, $className);
                $extendableAnnotation = $annotation;
            }
            elseif ($annotation instanceof InheritanceType
                 || $annotation instanceof DiscriminatorColumn
                 || $annotation instanceof DiscriminatorMap)
            {
                $hasDoctrineInheritanceMapping = true;
            }               
        }
        
        if ($extendableAnnotation && $hasDoctrineInheritanceMapping)
        {
            throw new ClarolineException(
                "@Extendable annotation isn't supported along with Doctrine "
                . "inheritance mapping in '{$className}'."
            );
        }
        
        return $extendableAnnotation;      
    }

    private function checkExtendableAnnotation(Extendable $annotation, $className)
    {
        if (! isset($annotation->discriminatorColumn))
        {
            throw new ClarolineException(
                "@Extendable annotation must have a 'discriminatorColumn' "
                . "attribute in '{$className}'."
            );
        }
        
        if (empty($annotation->discriminatorColumn))
        {
            throw new ClarolineException(
                "@Extendable annotation 'discriminatorColumn' attribute "
                . "must have a non empty value in '{$className}'."
            );
        }
    }  

    /** @return FileCacheReader */
    private function getReader()
    {
        return $this->container->get('annotation_reader');
    }
}