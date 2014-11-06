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

use Claroline\CoreBundle\Entity\Tool\Tool;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ToolMaskDecoderRepository")
 * @ORM\Table(name="claro_tool_mask_decoder")
 */
class ToolMaskDecoder
{
    const OPEN   = 1;
    const EDIT   = 2;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $value;

    /**
     * @ORM\Column()
     */
    protected $name;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\Tool",
     *     inversedBy="maskDecoders",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="tool_id", onDelete="CASCADE", nullable=false)
     */
    protected $tool;

    public function getId()
    {
        return $this->id;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTool()
    {
        return $this->tool;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setTool(Tool $tool)
    {
        $this->tool = $tool;
    }
}
