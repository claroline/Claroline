<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Entity;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_dropzonebundle_document")
 */
class Document
{
    use UuidTrait;

    const DOCUMENT_TYPE_FILE = 'file';
    const DOCUMENT_TYPE_TEXT = 'html';
    const DOCUMENT_TYPE_URL = 'url';
    const DOCUMENT_TYPE_RESOURCE = 'resource';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\DropZoneBundle\Entity\Drop",
     *      inversedBy="documents"
     * )
     * @ORM\JoinColumn(name="drop_id", nullable=false, onDelete="CASCADE")
     *
     * @var Drop
     */
    protected $drop;

    /**
     * @ORM\Column(name="document_type", type="text", nullable=false)
     *
     * @var string
     */
    protected $type;

    /**
     * @ORM\Column(name="file_array", type="json_array", nullable=true)
     *
     * @var array
     */
    protected $file;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $url;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    protected $content;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(name="resource_id", nullable=true, onDelete="SET NULL")
     *
     * @var ResourceNode
     */
    protected $resource;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", nullable=true, onDelete="SET NULL")
     *
     * @var User
     */
    protected $user;

    /**
     * @ORM\Column(name="drop_date", type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $dropDate;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\DropZoneBundle\Entity\DropzoneToolDocument",
     *     mappedBy="document"
     * )
     *
     * @var DropzoneToolDocument[]|ArrayCollection
     */
    protected $toolDocuments;

    /**
     * Document constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
        $this->toolDocuments = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return Drop
     */
    public function getDrop()
    {
        return $this->drop;
    }

    /**
     * @param Drop $drop
     */
    public function setDrop(Drop $drop)
    {
        $this->drop = $drop;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param array|null $file
     */
    public function setFile(array $file = null)
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return ResourceNode
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param ResourceNode|null $resource
     */
    public function setResource(ResourceNode $resource = null)
    {
        $this->resource = $resource;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return \DateTime
     */
    public function getDropDate()
    {
        return $this->dropDate;
    }

    /**
     * @param \DateTime $dropDate
     */
    public function setDropDate(\DateTime $dropDate)
    {
        $this->dropDate = $dropDate;
    }

    /**
     * @return array|ResourceNode|null|string
     */
    public function getData()
    {
        $data = null;

        switch ($this->type) {
            case self::DOCUMENT_TYPE_FILE:
                $data = $this->getFile();
                break;
            case self::DOCUMENT_TYPE_URL:
                $data = $this->getUrl();
                break;
            case self::DOCUMENT_TYPE_TEXT:
                $data = $this->getContent();
                break;
            case self::DOCUMENT_TYPE_RESOURCE:
                $data = $this->getResource();
                break;
        }

        return $data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        switch ($this->type) {
            case self::DOCUMENT_TYPE_FILE:
                $this->setFile($data);
                break;
            case self::DOCUMENT_TYPE_URL:
                $this->setUrl($data);
                break;
            case self::DOCUMENT_TYPE_TEXT:
                $this->setContent($data);
                break;
            case self::DOCUMENT_TYPE_RESOURCE:
                $this->setResource($data);
                break;
        }
    }

    /**
     * @return DropzoneToolDocument[]
     */
    public function getToolDocuments()
    {
        return $this->toolDocuments->toArray();
    }

    /**
     * @param DropzoneToolDocument $toolDocument
     */
    public function addToolDocument(DropzoneToolDocument $toolDocument)
    {
        if (!$this->toolDocuments->contains($toolDocument)) {
            $this->toolDocuments->add($toolDocument);
        }
    }

    /**
     * @param DropzoneToolDocument $toolDocument
     */
    public function removeToolDocument(DropzoneToolDocument $toolDocument)
    {
        if ($this->toolDocuments->contains($toolDocument)) {
            $this->toolDocuments->removeElement($toolDocument);
        }
    }

    public function emptyToolDocuments()
    {
        $this->toolDocuments->clear();
    }
}
