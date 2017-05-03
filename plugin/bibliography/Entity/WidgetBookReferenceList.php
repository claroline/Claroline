<?php

namespace Icap\BibliographyBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *      name="icap__bibliography_widget_book_reference_list",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="unique_widget_book_reference_list", columns={"resourceNode_id", "widgetInstance_id"})
 *      }
 * )
 * @ORM\Entity
 */
class WidgetBookReferenceList
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     *
     * @var \Claroline\CoreBundle\Entity\Resource\ResourceNode
     */
    protected $resourceNode;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetInstance")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @var \Claroline\CoreBundle\Entity\Widget\WidgetInstance
     */
    protected $widgetInstance;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ResourceNode
     */
    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    /**
     * @param ResourceNode $resourceNode
     *
     * @return WidgetBookReferenceList
     */
    public function setResourceNode($resourceNode)
    {
        $this->resourceNode = $resourceNode;

        return $this;
    }

    /**
     * @return WidgetInstance
     */
    public function getWidgetInstance()
    {
        return $this->widgetInstance;
    }

    /**
     * @param WidgetInstance $widgetInstance
     *
     * @return WidgetBookReferenceList
     */
    public function setWidgetInstance(WidgetInstance $widgetInstance)
    {
        $this->widgetInstance = $widgetInstance;

        return $this;
    }
}
