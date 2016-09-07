<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Entity;

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_cursusbundle_courses_widget_config")
 */
class CoursesWidgetConfig
{
    const MODE_LIST = 0;
    const MODE_CALENDAR = 1;
    const MODE_CHRONOLOGIC = 2;

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
     * @ORM\ManyToOne(targetEntity="Claroline\CursusBundle\Entity\Cursus")
     * @ORM\JoinColumn(name="cursus_id", nullable=true, onDelete="SET NULL")
     */
    protected $cursus;

    /**
     * @ORM\Column(name="default_mode", nullable=false, type="integer", options={"default" = 0})
     */
    protected $defaultMode = self::MODE_LIST;

    /**
     * @ORM\Column(name="public_sessions_only", nullable=false, type="boolean", options={"default" = 0})
     */
    protected $publicSessionsOnly = false;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $extra;

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

    public function getCursus()
    {
        return $this->cursus;
    }

    public function setCursus(Cursus $cursus = null)
    {
        $this->cursus = $cursus;
    }

    public function getDefaultMode()
    {
        return $this->defaultMode;
    }

    public function setDefaultMode($defaultMode)
    {
        $this->defaultMode = $defaultMode;
    }

    public function isPublicSessionsOnly()
    {
        return $this->publicSessionsOnly;
    }

    public function setPublicSessionsOnly($publicSessionsOnly)
    {
        $this->publicSessionsOnly = $publicSessionsOnly;
    }

    public function getExtra()
    {
        return $this->extra;
    }

    public function setExtra($extra)
    {
        $this->extra = $extra;
    }
}
