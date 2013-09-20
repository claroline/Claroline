<?php
/**
 * Created by : Vincent SAISSET
 * Date: 21/08/13
 * Time: 15:58
 */

namespace Icap\DropZoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="icap__dropzonebundle_document")
 */
class Document {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $url;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $path;
    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode"
     * )
     * @ORM\JoinColumn(name="resource_node_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $resourceNode;
    /**
     * @ORM\ManyToOne(
     *      targetEntity="Icap\DropZoneBundle\Entity\Drop",
     *      inversedBy="documents"
     * )
     * @ORM\JoinColumn(name="drop_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $drop;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    /**
     * @param mixed $resourceNode
     */
    public function setResourceNode($resourceNode)
    {
        $this->resourceNode = $resourceNode;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @param mixed $drop
     */
    public function setDrop($drop)
    {
        $this->drop = $drop;
    }

    /**
     * @return mixed
     */
    public function getDrop()
    {
        return $this->drop;
    }
}