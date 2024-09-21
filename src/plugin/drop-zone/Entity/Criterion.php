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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_dropzonebundle_criterion')]
#[ORM\Entity]
class Criterion
{
    use Id;
    use Uuid;

    /**
     * @var string
     */
    #[ORM\Column(name: 'instruction', type: 'text', nullable: false)]
    protected $instruction;

    /**
     *
     * @var Dropzone
     */
    #[ORM\JoinColumn(name: 'dropzone_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: \Claroline\DropZoneBundle\Entity\Dropzone::class, inversedBy: 'criteria')]
    protected $dropzone;

    public function __construct()
    {
        $this->refreshUuid();
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

    public function setDropzone(Dropzone $dropzone)
    {
        $this->dropzone = $dropzone;
    }
}
