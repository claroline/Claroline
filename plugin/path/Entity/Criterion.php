<?php

namespace Innova\PathBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Criterion.
 *
 * @ORM\Table(name="innova_stepcondition_criterion")
 * @ORM\Entity
 */
class Criterion implements \JsonSerializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="ctype", type="string", length=255)
     */
    private $ctype;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="string", length=255)
     */
    private $data;

    /**
     * Criteriagroup.
     *
     * @var \Innova\PathBundle\Entity\Criteriagroup
     *
     * @ORM\ManyToOne(targetEntity="Innova\PathBundle\Entity\Criteriagroup", inversedBy="criteria", cascade={"persist"})
     */
    protected $criteriagroup;

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
     * Set ctype.
     *
     * @param string $ctype
     *
     * @return Criterion
     */
    public function setCtype($ctype)
    {
        $this->ctype = $ctype;

        return $this;
    }

    /**
     * Get ctype.
     *
     * @return string
     */
    public function getCtype()
    {
        return $this->ctype;
    }

    /**
     * Set data.
     *
     * @param string $data
     *
     * @return Criterion
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data.
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set criteriagroup.
     *
     * @param \Innova\PathBundle\Entity\Criteriagroup $criteriagroup
     *
     * @return \Innova\PathBundle\Entity\Criterion
     */
    public function setCriteriagroup(Criteriagroup $criteriagroup = null)
    {
        if ($criteriagroup !== $this->criteriagroup) {
            $this->criteriagroup = $criteriagroup;

            if (null !== $criteriagroup) {
                $criteriagroup->addCriterion($this);
            }
        }

        return $this;
    }

    /**
     * Get criteriagroup.
     *
     * @return \Innova\PathBundle\Entity\Criterion
     */
    public function getCriteriagroup()
    {
        return $this->criteriagroup;
    }

    public function jsonSerialize()
    {
        // Initialize data array
        $jsonArray = [
            'id' => $this->id,                   // A local ID for the criterion in the criteriagroup
            'critid' => $this->id,                   // The real ID of the criterion into the DB
            'type' => $this->getCtype(),           // criterion type
            'data' => $this->getData(),            // criterion data
        ];

        return $jsonArray;
    }
}
