<?php

namespace Claroline\CoreBundle\Entity\Badge;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class BadgeRule
 *
 * @ORM\Table(name="claro_badge_rule")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Badge\BadgeRuleRepository")
 */
class BadgeRule
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
     */
    protected $id;

    /**
     * @var Badge[]
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Badge\Badge", inversedBy="badgeRules")
     * @ORM\JoinColumn(name="badge_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $badge;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    protected $occurrence;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $action;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $result;

    /**
     * @var string
     *
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $resultComparison;

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
     * @param \Claroline\CoreBundle\Entity\Badge\Badge $badge
     *
     * @return BadgeRule
     */
    public function setBadge($badge)
    {
        $this->badge = $badge;

        return $this;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Badge\Badge[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getBadge()
    {
        return $this->badge;
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
}
