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

use Symfony\Component\EventDispatcher\Event;

class GenericDataEvent extends Event
{
    private $data;
    private $response;

    public function __construct($data = null)
    {
        $this->data = $data;
        $this->response = null;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data = null)
    {
        $this->data = $data;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse($response = null)
    {
        $this->response = $response;
    }
}
