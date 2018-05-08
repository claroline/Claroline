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

// TODO : I don't understand why it is here
// TODO : I don't understand why there is a listener attached to the constructor (It should be a regular Exception)

class NoHttpRequestException extends \Exception
{
    /**
     * Sets the platform language.
     *
     * @DI\Observe("kernel.request")
     */
    public function __construct()
    {
        parent::__construct('This service is not available outside an http request');
    }
}
