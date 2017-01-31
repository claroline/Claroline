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

class EncodeFileEvent extends Event implements DataConveyorEventInterface
{
    private $resource;
    private $response;
    private $isPopulated = false;

    /**
     * Constructor.
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->isPopulated = true;
        $this->file = $file;
    }

    public function isPopulated()
    {
        return $this->isPopulated;
    }
}
