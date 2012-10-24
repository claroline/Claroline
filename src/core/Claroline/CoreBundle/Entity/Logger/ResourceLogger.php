<?php

namespace Claroline\CoreBundle\Entity\Logger;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ResourceLoggerRepository")
 * @ORM\Table(name="claro_resource_logger")
 */
class ResourceLogger
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceInstance")
     * @ORM\JoinColumn(name="instance_id", referencedColumnName="id")
     */
    protected $instance;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceType", cascade={"persist"})
     * @ORM\JoinColumn(name="resource_type_id", referencedColumnName="id")
     */
    protected $resourceType;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id")
     */
    protected $creator;

   /**
    * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
    * @ORM\JoinColumn(name="updator_id", referencedColumnName="id")
    */
    protected $updator;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace")
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id")
     */
    protected $workspace;

    /**
     * @ORM\Column(type="string", name="url")
     */
    protected $url;

    /**
     * @ORM\Column(type="string", name="action")
     */
    protected $action;

    /**
     * @ORM\Column(type="string", name="log_descr")
     */
    protected $logDescr;

    /**
     * @ORM\Column(type="datetime", name="date_log")
     * @Gedmo\Timestampable(on="create")
     */
    protected $dateLog;

    /**
     * @ORM\Column(type="string", name="path")
     */
    protected $path;

    public function getInstance()
    {
        return $this->instance;
    }

    public function setInstance($instance)
    {
        $this->instance = $instance;
    }

    public function getResourceType()
    {
        return $this->resourceType;
    }

    public function setResourceType($resourceType)
    {
        $this->resourceType = $resourceType;
    }

    public function getCreator()
    {
        return $this->creator;
    }

    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    public function getUpdator()
    {
        return $this->updator;
    }

    public function setUpdator($updator)
    {
        $this->updator = $updator;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getLogDescription()
    {
        return $this->logDescr;
    }

    public function setLogDescription($description)
    {
        $this->logDescr = $description;
    }

    public function getDate()
    {
        return $this->dateLog;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setWorkspace($workspace)
    {
        $this->workspace = $workspace;
    }

    public function getWorkspace()
    {
        return $this->workspace();
    }
}
