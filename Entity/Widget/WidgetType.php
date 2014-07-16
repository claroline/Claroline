<?php

namespace Icap\PortfolioBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\Plugin;

/**
 * @ORM\Table(name="icap__portfolio_widget_type")
 * @ORM\Entity(repositoryClass="Icap\PortfolioBundle\Repository\Widget\WidgetTypeRepository")
 */
class WidgetType
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false, unique=true)
     */
    protected $name;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_unique")
     */
    protected $isUnique = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_deletable")
     */
    protected $isDeletable = false;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $icon;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $name
     *
     * @return WidgetType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param boolean $isDeletable
     *
     * @return WidgetType
     */
    public function setIsDeletable($isDeletable)
    {
        $this->isDeletable = $isDeletable;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsDeletable()
    {
        return $this->isDeletable;
    }

    /**
     * @param boolean $isUnique
     *
     * @return WidgetType
     */
    public function setIsUnique($isUnique)
    {
        $this->isUnique = $isUnique;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsUnique()
    {
        return $this->isUnique;
    }

    /**
     * @param mixed $icon
     *
     * @return WidgetType
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }
}
