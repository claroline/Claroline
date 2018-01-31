<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Entity;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_dropzonebundle_criterion")
 */
class Criterion
{
    use UuidTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(name="instruction", type="text", nullable=false)
     *
     * @var string
     */
    protected $instruction;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\DropZoneBundle\Entity\Dropzone",
     *      inversedBy="criteria"
     * )
     * @ORM\JoinColumn(name="dropzone_id", nullable=false, onDelete="CASCADE")
     *
     * @var Dropzone
     */
    protected $dropzone;

    /**
     * Criterion constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getInstruction()
    {
        return $this->instruction;
    }

    /**
     * @param string $instruction
     */
    public function setInstruction($instruction)
    {
        $this->instruction = $instruction;
    }

    /**
     * @return Dropzone
     */
    public function getDropzone()
    {
        return $this->dropzone;
    }

    /**
     * @param Dropzone $dropzone
     */
    public function setDropzone(Dropzone $dropzone)
    {
        $this->dropzone = $dropzone;
    }
}
