<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Analytics;

use Claroline\CoreBundle\Event\DataConveyorEventInterface;
use Claroline\CoreBundle\Event\MandatoryEventInterface;
use Symfony\Component\EventDispatcher\Event;

class PlatformContentItemDetailsEvent extends Event implements DataConveyorEventInterface, MandatoryEventInterface
{
    /**
     * @var bool
     */
    private $isPopulated = false;

    /**
     * @var string
     */
    private $content = '';

    /**
     * @return bool
     */
    public function isPopulated()
    {
        return $this->isPopulated;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->isPopulated = true;
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
