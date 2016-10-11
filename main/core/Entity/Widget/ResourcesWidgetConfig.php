<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_resources_widget_config")
 */
class ResourcesWidgetConfig
{
    const DIRECTORY_MODE = 0;
    const TAG_MODE = 1;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetInstance")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $widgetInstance;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $details;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        return $this->id = $id;
    }

    public function getWidgetInstance()
    {
        return $this->widgetInstance;
    }

    public function setWidgetInstance(WidgetInstance $widgetInstance)
    {
        $this->widgetInstance = $widgetInstance;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function setDetails($details)
    {
        $this->details = $details;
    }

    public function getMode()
    {
        return !is_null($this->details) && isset($this->details['mode']) ? $this->details['mode'] : self::DIRECTORY_MODE;
    }

    public function setMode($mode)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['mode'] = $mode;
    }

    public function getDirectories()
    {
        return !is_null($this->details) && isset($this->details['directories']) ? $this->details['directories'] : [];
    }

    public function addDirectory($directoryId)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        if (!isset($this->details['directories'])) {
            $this->details['directories'] = [];
        }
        foreach ($this->details['directories'] as $id) {
            if ($id === $directoryId) {
                return;
            }
        }
        $this->details['directories'][] = $directoryId;
    }

    public function clearDirectories()
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['directories'] = [];
    }

    public function getTags()
    {
        return !is_null($this->details) && isset($this->details['tags']) ? $this->details['tags'] : [];
    }

    public function addTag($tag)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        if (!isset($this->details['tags'])) {
            $this->details['tags'] = [];
        }
        foreach ($this->details['tags'] as $t) {
            if ($t === $tag) {
                return;
            }
        }
        $this->details['tags'][] = $tag;
    }

    public function clearTags()
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['tags'] = [];
    }
}
