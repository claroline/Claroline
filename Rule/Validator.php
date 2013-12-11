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

use Claroline\CoreBundle\Rule\Constraints\OccurenceConstraint;
use Claroline\CoreBundle\Rule\Constraints\ResourceConstraint;
use Claroline\CoreBundle\Rule\Constraints\ResultConstraint;
use Claroline\CoreBundle\Rule\Entity\Rule;
use Claroline\CoreBundle\Rule\Rulable;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Repository\Log\LogRepository;
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
     * @DI\InjectParams({
     *     "logRepository" = @DI\Inject("claroline.repository.log"),
     * })
     */
    public function __construct(LogRepository $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    /**
     * @param Rulable $rulable
     * @param User    $user
     *
     * @return bool|Log[]
     */
    public function validate(Rulable $rulable, User $user)
    {
        return $this->validateRules($rulable->getRules(), $user, $rulable->getRestriction());
    }

    /**
     * @param \Claroline\CoreBundle\Rule\Entity\Rule[]  $rules
     * @param User  $user
     * @param array $restriction
     *
     * @return array|bool
     */
    protected function validateRules(array $rules, User $user, array $restriction)
    {
        $return    = array();
        $isChecked = true;

        if (0 < count($rules)) {
            foreach ($rules as $rule) {
                $rule->setUser($user);
                $checkedLogs = $this->validateRule($rule, $restriction);

                if (false === $checkedLogs) {
                    $isChecked = false;
                }
                else {
                    foreach ($checkedLogs as $checkedLog) {
                        $return[] = $checkedLog;
                    }
                }
            }
        }
        else {
            $isChecked = false;
        }

        return (false === $isChecked) ? false : $return;
    }

    /**
     * @param \Claroline\CoreBundle\Rule\Entity\Rule $rule
     * @param array                                  $restrictions
     *
     * @return bool|Log[]
     */
    public function validateRule(Rule $rule, array $restrictions = array())
    {
        $isValid            = true;
        /** @var \Claroline\CoreBundle\Rule\Constraints\AbstractConstraint[] $constraints */
        $constraints        = array();
        /** @var \Claroline\CoreBundle\Rule\Constraints\AbstractConstraint[] $existedConstraints */
        $existedConstraints = array(
            new OccurenceConstraint(),
            new ResultConstraint(),
            new ResourceConstraint()
        );

        foreach ($existedConstraints as $existedConstraint) {
            if ($existedConstraint->isApplicableTo($rule)) {
                $constraints[] = $existedConstraint;
            }
        }

        $queryBuilder = $this->buildQuery($constraints, $restrictions);

        /** @var \Claroline\CoreBundle\Entity\Log\Log[] $associatedLogs */
        $associatedLogs = $queryBuilder->getQuery()->getResult();

        foreach ($constraints as $constraint) {
            $constraint
                ->setRule($rule)
                ->setAssociatedLogs($associatedLogs);

            $isValid = $isValid && $constraint->validate();
        }

        return ($isValid) ? $associatedLogs : $isValid;
    }

    /**
     * @param \Claroline\CoreBundle\Rule\Constraints\AbstractConstraint[] $constraints
     *
     * @param array                                                       $restrictions
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function buildQuery(array $constraints, array $restrictions = null)
    {
        /** @var \Doctrine\ORM\QueryBuilder $queryBuilder */
        $queryBuilder = $queryBuilder = $this->logRepository->defaultQueryBuilderForBadge();

        foreach ($restrictions as $key => $restriction) {
            $queryBuilder
                ->andWhere(sprintf("l.%s = :%s", $key, $key))
                ->setParameter($key, $restriction);
        }

        foreach ($constraints as $constraint) {
            $queryBuilder = $constraint->getQuery($queryBuilder);
        }

        return $queryBuilder;
    }
}
