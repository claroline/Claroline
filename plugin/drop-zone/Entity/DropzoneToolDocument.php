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
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_dropzonebundle_tool_document")
 */
class DropzoneToolDocument
{
    use UuidTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\DropZoneBundle\Entity\Document",
     *     inversedBy="toolDocuments"
     * )
     * @ORM\JoinColumn(name="document_id", nullable=false, onDelete="CASCADE")
     */
    protected $document;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\DropZoneBundle\Entity\DropzoneTool"
     * )
     * @ORM\JoinColumn(name="tool_id", nullable=false, onDelete="CASCADE")
     */
    protected $tool;

    /**
     * @ORM\Column(name="tool_data", type="json_array", nullable=true)
     */
    protected $data;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getTool()
    {
        return $this->tool;
    }

    public function setTool(DropzoneTool $tool)
    {
        $this->tool = $tool;
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function setDocument(Document $document = null)
    {
        $this->document = $document;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }
}
