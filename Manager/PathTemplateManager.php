<?php

namespace Innova\PathBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\Path\PathTemplate;

/**
 * Path templates Manager
 * Manages life cycle of templates
 * @author Innovalangues <contact@innovalangues.net>
 */
class PathTemplateManager
{
    /**
     * Current entity manage for data persist
     * @var \Doctrine\Common\Persistence\ObjectManager $om
     */
    protected $om;
    
    /**
     * Class constructor - Inject required services
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->om = $objectManager;
    }
    
    /**
     * Find all available templates
     * @return array
     */
    public function findAll()
    {
        $results = $this->om->getRepository('InnovaPathBundle:Path\PathTemplate')->findAll();
        
        $templates = array();
        foreach ($results as $result) {
            $template = new \stdClass();
            
            $template->id          = $result->getId();
            $template->name        = $result->getName();
            $template->description = $result->getDescription();
            $template->structure   = json_decode($result->getStructure());
        
            $templates[] = $template;
        }
        
        return $templates;
    }
    
    public function create(PathTemplate $pathTemplate)
    {
        $this->om->persist($pathTemplate);
        $this->om->flush();
        
        return $pathTemplate;
    }
    
    public function edit(PathTemplate $pathTemplate)
    {
        $this->om->persist($pathTemplate);
        $this->om->flush();
        
        return $pathTemplate;
    }
    
    /**
     * Delete template from DB
     * @param  \Innova\PathBundle\Entity\Path\PathTemplate $pathTemplate
     * @return boolean
     */
    public function delete(PathTemplate $pathTemplate)
    {
        $this->om->remove($pathTemplate);
        $this->om->flush();
        
        return $this;
    }
}