<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Tool;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Tool\ToolRepository")
 * @ORM\Table(
 *      name="claro_tools",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="tool_plugin_unique",columns={"name", "plugin_id"})}
 * )
 */
class Tool extends AbstractTool
{
    /**
     * @ORM\Column(name="is_displayable_in_workspace", type="boolean")
     */
    protected $isDisplayableInWorkspace = true;

    /**
     * @ORM\Column(name="is_displayable_in_desktop", type="boolean")
     */
    protected $isDisplayableInDesktop = true;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder",
     *     mappedBy="tool",
     *     cascade={"persist", "remove"}
     * )
     *
     * @var ToolMaskDecoder[]|ArrayCollection
     */
    protected $maskDecoders;

    public function __construct()
    {
        parent::__construct();

        $this->maskDecoders = new ArrayCollection();
    }

    public function setDisplayableInWorkspace($bool)
    {
        $this->isDisplayableInWorkspace = $bool;

        return $this;
    }

    public function isDisplayableInWorkspace()
    {
        return $this->isDisplayableInWorkspace;
    }

    public function setDisplayableInDesktop($bool)
    {
        $this->isDisplayableInDesktop = $bool;

        return $this;
    }

    public function isDisplayableInDesktop()
    {
        return $this->isDisplayableInDesktop;
    }

    public function addMaskDecoder(ToolMaskDecoder $maskDecoder)
    {
        $this->maskDecoders->add($maskDecoder);
    }

    public function getMaskDecoders()
    {
        return $this->maskDecoders;
    }
}
