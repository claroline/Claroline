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

use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Tool\ToolMaskDecoderRepository")
 * @ORM\Table(
 *     name="claro_tool_mask_decoder",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="tool_mask_decoder_unique_tool_and_name",
 *             columns={"tool_id", "name"}
 *         )
 *     })
 */
class ToolMaskDecoder
{
    use Id;
    public static $defaultActions = ['open', 'edit', 'administrate'];
    public static $defaultValues = [
        'open' => 1,
        'edit' => 2,
        'administrate' => 4,
    ];

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

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return Tool
     */
    public function getTool()
    {
        return $this->tool;
    }

    public function setTool(Tool $tool)
    {
        $this->tool = $tool;
    }
}
