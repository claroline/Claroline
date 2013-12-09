<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Rule\Entity;

use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\MappedSuperclass
 * @ExclusionPolicy("all")
 */
abstract class Rule
{
    const RESULT_EQUAL          = '=';
    const RESULT_INFERIOR       = '<';
    const RESULT_INFERIOR_EQUAL = '<=';
    const RESULT_SUPERIOR       = '>';
    const RESULT_SUPERIOR_EQUAL = '>=';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=false)
     * @Expose
     */
    protected $occurrence;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     * @Expose
     */
    protected $action;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Expose
     */
    protected $result;

    /**
     * @var string
     *
     * @ORM\Column(type="smallint", nullable=true)
     * @Expose
     */
    protected $resultComparison;

    /**
     * @var ResourceNode
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $resource;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $action
     *
     * @return BadgeRule
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param int $occurrence
     *
     * @return BadgeRule
     */
    public function setOccurrence($occurrence)
    {
        $this->occurrence = $occurrence;

        return $this;
    }

    /**
     * @return int
     */
    public function getOccurrence()
    {
        return $this->occurrence;
    }

    /**
     * @param string $result
     *
     * @return BadgeRule
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param string $resultComparison
     *
     * @return BadgeRule
     */
    public function setResultComparison($resultComparison)
    {
        $this->resultComparison = $resultComparison;

        return $this;
    }

    /**
     * @return string
     */
    public function getResultComparison()
    {
        return $this->resultComparison;
    }

    /**
     * @return array
     */
    public static function getResultComparisonTypes()
    {
        return array(self::RESULT_EQUAL,
                     self::RESULT_INFERIOR,
                     self::RESULT_INFERIOR_EQUAL,
                     self::RESULT_SUPERIOR,
                     self::RESULT_SUPERIOR_EQUAL);
    }

    /**
     * @param mixed $resource
     *
     * @return BadgeRule
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }
}
