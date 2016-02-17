<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation;

class ExecutorException extends \Exception
{
    const REPO_NOT_FOUND = 11;
    const REPO_NOT_JSON = 12;
    const REPO_NOT_ARRAY = 13;
}
