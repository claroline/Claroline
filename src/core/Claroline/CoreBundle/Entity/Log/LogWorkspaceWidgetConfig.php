<?php

namespace Claroline\CoreBundle\Entity\Log;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_log_workspace_widget_config")
 */
class LogWorkspaceWidgetConfig
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="is_default", type="boolean")
     */
    protected $isDefault = false;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace")
     */
    protected $workspace;

    /**
     * @ORM\Column(type="integer")
     */
    protected $amount = 5;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    protected $restrictions = array();

    public function copy (LogWorkspaceWidgetConfig $source = null)
    {
        if ($source !== null) {
            $this
                ->setRestrictions($source->getRestrictions())
                ->setAmount($source->getAmount())
                ->setWorkspace($source->getWorkspace());
        }
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set workspace
     *
     * @param  AbstractWorkspace $workspace
     * @return LogWorkspaceWidgetConfig
     */
    public function setWorkspace(AbstractWorkspace $workspace = null)
    {
        $this->workspace = $workspace;

        return $this;
    }

    /**
     * Get workspace
     *
     * @return AbstractWorkspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * Set isDefault
     *
     * @param  boolean                  $isDefault
     * @return LogWorkspaceWidgetConfig
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get isDefault
     *
     * @return boolean
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set amount
     *
     * @param  integer                  $amount
     * @return LogWorkspaceWidgetConfig
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return integer
     */
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
     * @param integer $maxRestrictions
     *
     * @return bool
     */
    public function hasAllRestriction($maxRestrictions)
    {
        return count($this->restrictions) === $maxRestrictions;
    }
}
