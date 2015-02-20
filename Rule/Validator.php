<?php

namespace Icap\BadgeBundle\Rule;

use Claroline\CoreBundle\Rule\Constraints\ActionConstraint;
use Claroline\CoreBundle\Rule\Constraints\BadgeConstraint;
use Claroline\CoreBundle\Rule\Constraints\DoerConstraint;
use Claroline\CoreBundle\Rule\Constraints\OccurenceConstraint;
use Claroline\CoreBundle\Rule\Constraints\ReceiverConstraint;
use Claroline\CoreBundle\Rule\Constraints\RuleActiveDateConstraint;
use Claroline\CoreBundle\Rule\Constraints\ResourceConstraint;
use Claroline\CoreBundle\Rule\Constraints\ResultConstraint;
use Claroline\CoreBundle\Rule\Entity\Rule;
use Claroline\CoreBundle\Rule\Rulable;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Log\Log;
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
                    $return['validRules']++;
                    $return['rules'][] = array(
                        'rule' => $rule, 'logs' => $checkedLogs
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
        $usedConstraints    = array();
        /** @var \Claroline\CoreBundle\Rule\Constraints\AbstractConstraint[] $existedConstraints */
        $existedConstraints = array(
            new OccurenceConstraint(),
            new ResultConstraint(),
            new ResourceConstraint(),
            new DoerConstraint(),
            new ReceiverConstraint(),
            new ActionConstraint(),
            new BadgeConstraint(),
            new RuleActiveDateConstraint()
        );

        foreach ($existedConstraints as $existedConstraint) {
            if ($existedConstraint->isApplicableTo($rule)) {
                $usedConstraints[] = $existedConstraint->setRule($rule);
            }
        }

        $validatedConstraints = 0;
        $nbConstraints        = count($usedConstraints);

        $associatedLogs = $this->getAssociatedLogs($usedConstraints, $restrictions);

        foreach ($usedConstraints as $usedConstraint) {
            $usedConstraint->setAssociatedLogs($associatedLogs);

            if ($usedConstraint->validate()) {
                $validatedConstraints++;
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
        $queryBuilder = $this->logRepository->defaultQueryBuilderForBadge();

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
