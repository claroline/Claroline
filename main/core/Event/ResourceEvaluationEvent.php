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

use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched when an activity evaluation is created or updated.
 */
class ResourceEvaluationEvent extends Event
{
    private $evaluation;

    public function __construct(ResourceUserEvaluation $evaluation)
    {
        $this->evaluation = $evaluation;
    }

    public function getEvaluation()
    {
        return $this->evaluation;
    }
}
