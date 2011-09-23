<?php

namespace Claroline\CommonBundle\Service\ORM\DynamicInheritance\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Annotations\FileCacheReader;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Claroline\CommonBundle\Service\ORM\DynamicInheritance\Annotation\Extendable;

class ExtendableListener extends ContainerAware implements EventSubscriber
{
    // metadatas of the @extendable entities
    private $extendables;
    
    public function __construct()
    {
        $this->extendables = array();
    }
    
    public function setAnnotationReader(FileCacheReader $reader)
    {
        $this->annotationReader = $reader;
    }
    
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
            $discriminatorColumn = $extendableAnnotation->discriminatorColumn;
            $classMetadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_JOINED);
            $classMetadata->setDiscriminatorColumn(
                array('name' => $discriminatorColumn, 'length' => 255)
            );
            $classMetadata->setDiscriminatorMap(array($classDiscriminator => $className));
            $this->extendables[$className] = $classMetadata;
        }
        
        foreach ($this->extendables as $extendableClassMetadata)
        {
            if (get_parent_class($className) === $extendableClassMetadata->getName())
            {
                // the entity is a child of an @extendable entity
                $classMetadata->discriminatorValue = $classDiscriminator;           
                $classMetadata->discriminatorMap[$classDiscriminator] = $className; 
                $extendableClassMetadata->discriminatorMap[$classDiscriminator] = $className;
                $extendableClassMetadata->subClasses[] = $className;
            }
        }  
    }
    
    /**
     * @todo check that no other inheritance mapping is involved 
     *       (avoid conflict with Doctrine own annotations)
     * @todo check that the discriminator column value is valid
     */
    private function getExtendableAnnotation($className)
    {
        $reflectionClass = new \ReflectionClass($className);
        $annotations = $this->annotationReader->getClassAnnotations($reflectionClass);
            
        foreach ($annotations as $annotation)
        {
            if ($annotation instanceof Extendable)
            {
                return $annotation;
            }
        }
        
        return false;
    }

    /*
    // Extract (with light modifications ) from :
    // http://thoughtsofthree.com/2011/04/defining-discriminator-maps-at-child-level-in-doctrine-2-0/
    // (still unavailable ; kept just in case)
    
    private $em;        // Doctrine EntityManager
    private $driver;    // Doctrines Metadata Driver  
    private $map;       // Temporary map for calculations  
    private $cachedMap; // Cached map for fast lookups  
    private $annotationReader;

    public function __construct()
    {
        $this->cachedMap = array();
    }
    
    public function setAnnotationReader(FileCacheReader $reader)
    {
        $this->annotationReader = $reader;
    }
    
    public function getSubscribedEvents() 
    {
        return array(Events::loadClassMetadata);  
    }  
    
    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        if ($this->em === null)
        {
            $this->em = $this->container->get('doctrine.orm.entity_manager');
        }
        
        $this->driver = $this->em->getConfiguration()->getMetadataDriverImpl();
        
        // Reset the temporary calculation map and get the classname  
        $this->map = array();
        $class = $event->getClassMetadata()->name;

        // Lookup whether we already calculated the map for this element  
        if (\array_key_exists($class, $this->cachedMap))
        {
            $this->overrideMetadata($event, $class);
            return;
        }

        // Check whether we have to process this class  
        if (count($event->getClassMetadata()->discriminatorMap) == 0
            && $this->extractEntry($class))
        {
            // Now build the whole map  
            $this->checkFamily($class);
        } 
        else
        {
            // Nothing to do...  
            return;
        }

        // Create the lookup entries  
        $dMap = array_flip($this->map);
        foreach ($this->map as $cName => $discr)
        {
            $this->cachedMap[$cName]['map'] = $dMap;
            $this->cachedMap[$cName]['discr'] = $this->map[$cName];
        }

        // Override the data for this class  
        $this->overrideMetadata($event, $class);
    }
    
    private function extractEntry($class)
    {
        //$annotations = Annotation::getAnnotationsForClass($class);
        $annotations = $this->annotationReader->getClassAnnotations(new \ReflectionClass($class));
        $success = false;
        foreach($annotations as $annotation)
        {
            if(! $annotation instanceof DiscriminatorEntry)
            {
                continue;
            }
            $value = $annotation->value;
            if (\in_array($value, $this->map))
            {
                throw new \Exception("Found duplicate discriminator map entry '" . $value . "' in " . $class);
            }

            $this->map[$class] = $value;
            $success = true;
        }       

        return $success;
    }

    private function checkFamily($class)
    {
        $rc = new \ReflectionClass($class);
        $parentClass = $rc->getParentClass();
        $parent = $parentClass['name'];

        if ($parent !== false && $parent !== null)
        {
            // Also check all the children of our parent  
            $this->checkFamily($parent);
        } else
        {
            // This is the top-most parent, used in overrideMetadata  
            $this->cachedMap[$class]['isParent'] = true;

            // Find all the children of this class  
            $this->checkChildren($class);
        }
    }

    private function checkChildren($class)
    {
        foreach ($this->driver->getAllClassNames() as $name)
        {
            $cRc = new \ReflectionClass($name);
            
            // ?
            //if (! $cRc->getParentClass())
            //{
            //    return;
            //}
            
            $cParent = $cRc->getParentClass()->name;

            // Check if we already had this class, if its a child and if it has the annotation  
            if (!\array_key_exists($name, $this->map)
                && $cParent == $class && $this->extractEntry($name))
            {
                // This child might again have children...  
                $this->checkChildren($name);
            }
        }
    }

    private function overrideMetadata(LoadClassMetadataEventArgs $event, $class)
    {
        // Set the discriminator map and value  
        $event->getClassMetadata()->discriminatorMap =
            $this->cachedMap[$class]['map'];
        $event->getClassMetadata()->discriminatorValue =
            $this->cachedMap[$class]['discr'];

        // If we are the top-most parent, set subclasses!  
        if (isset($this->cachedMap[$class]['isParent'])
            && $this->cachedMap[$class]['isParent'] === true)
        {
            // Remove yourself from the map, set this as subclasses, but only the values!  
            $subclasses = $this->cachedMap[$class]['map'];
            unset($subclasses[$this->cachedMap[$class]['discr']]);
            $event->getClassMetadata()->subClasses =
                array_values($subclasses);
        }
    }*/
}