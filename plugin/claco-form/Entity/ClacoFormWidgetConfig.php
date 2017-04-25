<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_clacoformbundle_claco_form_widget_config")
 */
class ClacoFormWidgetConfig
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetInstance")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $widgetInstance;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(name="claco_form_id", nullable=true, onDelete="SET NULL")
     */
    protected $resourceNode;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\ClacoFormBundle\Entity\Field")
     * @ORM\JoinTable(name="claro_clacoformbundle_claco_form_widget_config_field")
     */
    protected $fields;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $options;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
    }
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getWidgetInstance()
    {
        return $this->widgetInstance;
    }

    public function setWidgetInstance(WidgetInstance $widgetInstance)
    {
        $this->widgetInstance = $widgetInstance;
    }

    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    public function setResourceNode(ResourceNode $resourceNode = null)
    {
        $this->resourceNode = $resourceNode;
    }

    public function getFields()
    {
        return $this->fields->toArray();
    }

    public function addField(Field $field)
    {
        if (!$this->fields->contains($field)) {
            $this->fields->add($field);
        }

        return $this;
    }

    public function removeField(Field $field)
    {
        if ($this->fields->contains($field)) {
            $this->fields->removeElement($field);
        }

        return $this;
    }

    public function emptyFields()
    {
        $this->fields->clear();
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function getNbEntries()
    {
        return !is_null($this->options) && isset($this->options['nb_entries']) ? $this->options['nb_entries'] : 0;
    }

    public function setNbEntries($nbEntries)
    {
        if (is_null($this->options)) {
            $this->options = [];
        }
        $this->options['nb_entries'] = $nbEntries;
    }

    public function getShowFieldLabel()
    {
        return !is_null($this->options) && isset($this->options['show_field_label']) ? $this->options['show_field_label'] : false;
    }

    public function setShowFieldLabel($showFieldLabel)
    {
        if (is_null($this->options)) {
            $this->options = [];
        }
        $this->options['show_field_label'] = $showFieldLabel;
    }

    public function getShowCreatorPicture()
    {
        return !is_null($this->options) && isset($this->options['show_creator_picture']) ? $this->options['show_creator_picture'] : false;
    }

    public function setShowCreatorPicture($showCreatorPicture)
    {
        if (is_null($this->options)) {
            $this->options = [];
        }
        $this->options['show_creator_picture'] = $showCreatorPicture;
    }

    public function getCategories()
    {
        return !is_null($this->options) && isset($this->options['categories']) ? $this->options['categories'] : [];
    }

    public function setCategories(array $categoriesIds)
    {
        if (is_null($this->options)) {
            $this->options = [];
        }
        $this->options['categories'] = $categoriesIds;
    }
}
