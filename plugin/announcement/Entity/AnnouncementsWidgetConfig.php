<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Entity;

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_announcements_widget_config")
 */
class AnnouncementsWidgetConfig
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetInstance")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $widgetInstance;

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

    public function getAnnouncements()
    {
        return !is_null($this->details) && isset($this->details['announcements']) ? $this->details['announcements'] : [];
    }

    public function addAnnouncement($announcementId)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        if (!isset($this->details['announcements'])) {
            $this->details['announcements'] = [];
        }
        foreach ($this->details['announcements'] as $id) {
            if ($id === $announcementId) {
                return;
            }
        }
        $this->details['announcements'][] = $announcementId;
    }

    public function clearAnnouncements()
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['announcements'] = [];
    }
}
