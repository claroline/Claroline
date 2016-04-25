<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Rule;

use Claroline\CoreBundle\Rule\Constraints\AbstractConstraint;
use Claroline\CoreBundle\Rule\Entity\Rule;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Repository\Log\LogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.rule.validator")
 */
class Validator
{
    /**
     * @var LogRepository
     */
    private $logRepository;

    /**
     * @var AbstractConstraint[]
     */
    protected $constraints;

    /**
     * @DI\InjectParams({
     *     "logRepository" = @DI\Inject("claroline.repository.log"),
     * })
     */
    public function __construct(LogRepository $logRepository)
    {
        $this->logRepository = $logRepository;
        $this->constraints = new ArrayCollection();
    }

    /**
     * @return Constraints\AbstractConstraint[]
     */
    public function getConstraints()
    {
        return $this->constraints;
    }

    /**
     * @param Constraints\AbstractConstraint[] $constraints
     *
     * @return Validator
     */
    public function setConstraints($constraints)
    {
        $this->constraints = $constraints;

        return $this;
    }

    /**
     * @param AbstractConstraint $constraint
     *
     * @return bool
     */
    public function addConstraint(AbstractConstraint $constraint)
    {
        return $this->constraints->add($constraint);
    }

    /**
     * @param Rulable $rulable
     * @param User    $user
     *
     * @return array
     */
    public function validate(Rulable $rulable, User $user)
    {
        return $this->validateRules($rulable->getRules(), $user, $rulable->getRestriction());
    }

    /**
     * @param \Claroline\CoreBundle\Rule\Entity\Rule[] $rules
     * @param User                                     $user
     * @param array                                    $restriction
     *
     * @return array|bool
     */
    protected function validateRules($rules, User $user, array $restriction)
    {
        $return = array('validRules' => 0, 'rules' => array());

        if (0 < count($rules)) {
            foreach ($rules as $rule) {
                $rule->setUser($user);
                $checkedLogs = $this->validateRule($rule, $restriction);

                if (false !== $checkedLogs) {
                    ++$return['validRules'];
                    $return['rules'][] = array(
                        'rule' => $rule, 'logs' => $checkedLogs,
                    );
                }
            }
        }

        return $return;
    }

    /**
     * @param \Claroline\CoreBundle\Rule\Entity\Rule $rule
     * @param array                                  $restrictions
     *
     * @return bool|Log[]
     */
    public function validateRule(Rule $rule, array $restrictions = array())
    {
        /** @var \Claroline\CoreBundle\Rule\Constraints\AbstractConstraint[] $usedConstraints */
        $usedConstraints = array();
        $existedConstraints = $this->getConstraints();

        foreach ($existedConstraints as $existedConstraint) {
            if ($existedConstraint->isApplicableTo($rule)) {
                $usedConstraints[] = $existedConstraint->setRule($rule);
            }
        }

        $validatedConstraints = 0;
        $nbConstraints = count($usedConstraints);

        $associatedLogs = $this->getAssociatedLogs($usedConstraints, $restrictions);

        foreach ($usedConstraints as $usedConstraint) {
            $usedConstraint->setAssociatedLogs($associatedLogs);

            if ($usedConstraint->validate()) {
                ++$validatedConstraints;
            }
        }

        return ($validatedConstraints === $nbConstraints) ? $associatedLogs : false;
    }

    /**
     * @param \Claroline\CoreBundle\Rule\Constraints\AbstractConstraint[] $constraints
     * @param array                                                       $restrictions
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function buildQuery(array $constraints, array $restrictions = null)
    {
        /** @var \Doctrine\ORM\QueryBuilder $queryBuilder */
        $queryBuilder = $this->logRepository
            ->createQueryBuilder('l')->orderBy('l.dateLog');

        foreach ($restrictions as $key => $restriction) {
            $queryBuilder
                ->andWhere(sprintf('l.%s = :%s', $key, $key))
                ->setParameter($key, $restriction);
        }

        foreach ($constraints as $constraint) {
            $queryBuilder = $constraint->getQuery($queryBuilder);
        }

        return $queryBuilder;
    }

    /**
     * @param \Claroline\CoreBundle\Rule\Constraints\AbstractConstraint[] $constraints
     * @param array                                                       $restrictions
     *
     * @return \Claroline\CoreBundle\Entity\Log\Log[]
     */
    protected function getAssociatedLogs(array $constraints, array $restrictions = null)
    {
        $queryBuilder = $this->buildQuery($constraints, $restrictions);

        return $queryBuilder->getQuery()->getResult();
    }
}
