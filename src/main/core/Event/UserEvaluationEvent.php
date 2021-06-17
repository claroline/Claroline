<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event;

use Claroline\EvaluationBundle\Entity\Evaluation\AbstractUserEvaluation;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when an activity evaluation is created or updated.
 */
class UserEvaluationEvent extends Event
{
    /** @var AbstractUserEvaluation */
    private $evaluation;

    /**
     * ResourceEvaluationEvent constructor.
     */
    public function __construct(AbstractUserEvaluation $evaluation)
    {
        $this->evaluation = $evaluation;
    }

    /**
     * Get the current evaluation.
     *
     * @return AbstractUserEvaluation
     */
    public function getEvaluation()
    {
        return $this->evaluation;
    }
}
