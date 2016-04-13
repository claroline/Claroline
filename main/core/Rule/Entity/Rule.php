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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\MappedSuperclass
 * @ExclusionPolicy("all")
 */
abstract class Rule
{
    const RESULT_EQUAL = '=';
    const RESULT_INFERIOR = '<';
    const RESULT_INFERIOR_EQUAL = '<=';
    const RESULT_SUPERIOR = '>';
    const RESULT_SUPERIOR_EQUAL = '>=';

    const DOER_USER = 'doer';
    const RECEIVER_USER = 'receiver';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     */
    protected $id;

    /**
     * @var int
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
     * @ORM\Column(type="string", nullable=true)
     * @Expose
     */
    protected $resultMax;

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
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     * @Expose
     */
    protected $resource;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=false)
     * @Expose
     */
    protected $userType = 0;

    /**
     * @var \Claroline\CoreBundle\Entity\User
     */
    protected $user;

    /**
     * @var datetime
     *
     * @ORM\Column(name="active_from", type="datetime", nullable=true)
     */
    protected $activeFrom;

    /**
     * @var datetime
     *
     * @ORM\Column(name="active_until", type="datetime", nullable=true)
     */
    protected $activeUntil;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Rule
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param string $action
     *
     * @return Rule
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
     * @return Rule
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
     * @return Rule
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
     * @param string $resultMax
     *
     * @return Rule
     */
    public function setResultMax($resultMax)
    {
        $this->resultMax = $resultMax;

        return $this;
    }

    /**
     * @return string
     */
    public function getResultMax()
    {
        return $this->resultMax;
    }

    /**
     * @param int $resultComparison
     *
     * @return Rule
     */
    public function setResultComparison($resultComparison)
    {
        $this->resultComparison = $resultComparison;

        return $this;
    }

    /**
     * @return int
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
                     self::RESULT_SUPERIOR_EQUAL, );
    }

    /**
     * @param string $comparisonType
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    public static function getResultComparisonTypeValue($comparisonType)
    {
        $comparisonTypeValue = array_search($comparisonType, self::getResultComparisonTypes());

        if (false === $comparisonTypeValue) {
            throw new \InvalidArgumentException('Unknow comparison type.');
        }

        return $comparisonTypeValue;
    }

    /**
     * @param mixed $resource
     *
     * @return Rule
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

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return Rule
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @throws \RuntimeException
     *
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function getUser()
    {
        if (null === $this->user) {
            throw new \RuntimeException("No user given to the rule. Rule inevitably apply to a user, neither it's a doer or a receiver.");
        }

        return $this->user;
    }

    /**
     * @param int $userType
     *
     * @return Rule
     */
    public function setUserType($userType)
    {
        $this->userType = $userType;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserType()
    {
        return $this->userType;
    }

    /**
     * @return array
     */
    public static function getUserTypes()
    {
        return array(self::DOER_USER,
                     self::RECEIVER_USER, );
    }

    /**
     * @return bool
     */
    public function getIsUserReceiver()
    {
        return $this->userType === 1;
    }

    /**
     * @param bool $value
     *
     * @return Rule
     */
    public function setIsUserReceiver($value)
    {
        if ($value) {
            $this->userType = 1;
        } else {
            $this->userType = 0;
        }

        return $this;
    }

    /**
     * @param datetime $activeFrom
     *
     * @return Rule
     */
    public function setActiveFrom($activeFrom)
    {
        $this->activeFrom = $activeFrom;

        return $this;
    }

    /**
     * @return datetime
     */
    public function getActiveFrom()
    {
        return $this->activeFrom;
    }

    /**
     * @param datetime $activeUntil
     *
     * @return Rule
     */
    public function setActiveUntil($activeUntil)
    {
        $this->activeUntil = $activeUntil;

        return $this;
    }

    /**
     * @return datetime
     */
    public function getActiveUntil()
    {
        return $this->activeUntil;
    }
}
