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

use Claroline\CoreBundle\Security\Voter\AbstractVoter;

abstract class AbstractEvaluationVoter extends AbstractVoter
{
    // tool right allowing to show evaluations of other users
    const SHOW_EVALUATIONS = 'SHOW_EVALUATIONS';

    public function getSupportedActions(): array
    {
        return [self::OPEN, self::VIEW, self::EDIT, self::DELETE];
    }
}
