<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Security\Voter;

use Claroline\AppBundle\Security\Voter\AbstractVoter;

abstract class AbstractEvaluationVoter extends AbstractVoter
{
    public function getSupportedActions(): array
    {
        return [self::OPEN, self::VIEW, self::EDIT, self::DELETE];
    }
}
