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

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ToolMaskDecoderRepository")
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
    public static $defaultActions = array('open', 'edit');
    public static $defaultValues = array('open' => 1, 'edit' => 2);
    public static $defaultDeniedIconClass = array(
        'open' => 'fa fa-eye-slash',
        'edit' => 'fa fa-edit',
    );
    public static $defaultGrantedIconClass = array(
        'open' => 'fa fa-eye',
        'edit' => 'fa fa-edit',
    );

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

    /**
     * @ORM\Column(name="granted_icon_class")
     */
    protected $grantedIconClass;

    /**
     * @ORM\Column(name="denied_icon_class")
     */
    protected $deniedIconClass;

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

    public function getGrantedIconClass()
    {
        return $this->grantedIconClass;
    }

    public function getDeniedIconClass()
    {
        return $this->deniedIconClass;
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

    public function setGrantedIconClass($grantedIconClass)
    {
        $this->grantedIconClass = $grantedIconClass;
    }

    public function setDeniedIconClass($deniedIconClass)
    {
        $this->deniedIconClass = $deniedIconClass;
    }
}
