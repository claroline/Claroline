<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ResultBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="claro_result")
 * @ORM\Entity(repositoryClass="Claroline\ResultBundle\Repository\ResultRepository"))
 */
class Result extends AbstractResource
{
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date;

    /**
     * @ORM\OneToMany(targetEntity="Mark", mappedBy="result")
     */
    private $marks;

    /**
     * @ORM\Column(type="integer")
     */
    private $total;

    public function __construct(\DateTime $date = null, $total = null)
    {
        $this->date = $date;
        $this->total = $total;
        $this->marks = new ArrayCollection();
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        if ($this->date) {
            return $this->date;
        }

        return $this->getResourceNode()->getCreationDate();
    }

    /**
     * @param Mark $mark
     */
    public function addMark(Mark $mark)
    {
        $this->marks->add($mark);
    }

    /**
     * @return ArrayCollection
     */
    public function getMarks()
    {
        return $this->marks;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param int
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }
}
