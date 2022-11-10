<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Messenger\Message;

/**
 * Classes implementing this interface will
 * subscribe to the asynchronous low priority transport.
 *
 * It should be used to any transfer related feature (eg. workspace copy, transfer plugin) and other "heavy" tasks.
 */
interface AsyncLowMessageInterface
{
}
