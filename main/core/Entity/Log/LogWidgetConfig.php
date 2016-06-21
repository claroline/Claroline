<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Log;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Log\LogWidgetConfigRepository")
 * @ORM\Table(name="claro_log_widget_config")
 */
class LogWidgetConfig
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $amount = 5;

    /**
     * for the workspace.
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    protected $restrictions = array();

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetInstance")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $widgetInstance;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $amount
     *
     * @return SimpleTextWorkspaceConfig
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $restrictions
     *
     * @return LogWorkspaceWidgetConfig
     */
    public function setRestrictions($restrictions)
    {
        $this->restrictions = $restrictions;

        return $this;
    }

    /**
     * @return array
     */
    public function getRestrictions()
    {
        $restrictions = array();

        foreach ($this->restrictions as $restriction) {
            $restrictions[$restriction] = $restriction;
        }

        return $restrictions;
    }

    /**
     * @return bool
     */
    public function hasNoRestriction()
    {
        return count($this->restrictions) === 0;
    }

    /**
     * @return bool
     */
    public function hasRestriction()
    {
        return !$this->hasNoRestriction();
    }

    /**
     * @param int $maxRestrictions
     *
     * @return bool
     */
    public function hasAllRestriction($maxRestrictions)
    {
        return count($this->restrictions) === $maxRestrictions;
    }

    public function setWidgetInstance(WidgetInstance $ds)
    {
        $this->widgetInstance = $ds;
    }

    public function getWidgetInstance()
    {
        return $this->widgetInstance;
    }

    public function copy(LogWidgetConfig $source = null)
    {
        if ($source !== null) {
            $this->setRestrictions($source->getRestrictions());
            $this->setAmount($source->getAmount());
        }
    }
}
