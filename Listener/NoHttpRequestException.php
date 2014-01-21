<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

class NoHttpRequestException extends \Exception
{
    public function __construct()
    {
        parent::__construct('This service is not available outside an http request');
    }
}
