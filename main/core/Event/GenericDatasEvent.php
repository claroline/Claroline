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

class GenericDatasEvent extends Event
{
    private $datas;
    private $response;

    public function __construct($datas = null)
    {
        $this->datas = $datas;
        $this->response = null;
    }

    public function getDatas()
    {
        return $this->datas;
    }

    public function setDatas($datas = null)
    {
        $this->datas = $datas;
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
