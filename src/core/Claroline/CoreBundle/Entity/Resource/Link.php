<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Claroline\CoreBundle\Entity\Resource\ResourceType;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_link")
 */
class Link extends AbstractResource
{
    /**
     * @ORM\Column(type="string")
     */
    private $url;
    
    /** @var ResourceType */
    private $resourceType;
    
    public function getUrl()
    {
        return $this->url;
    }
    
    public function setUrl($url)
    {
        $this->url = $url;
    }
    
    public function getResourceType()
    {
        return $this->resourceType;
    }
    
    public function setResourceType(ResourceType $resourceType)
    {
        $this->resourceType = $resourceType;
    }
}