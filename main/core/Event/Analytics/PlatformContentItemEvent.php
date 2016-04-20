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

use Symfony\Component\EventDispatcher\Event;

class PlatformContentItemEvent extends Event
{
    /**
     * @var array
     */
    private $items = [];

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param array $newItem
     *
     * @return PlatformContentItemEvent
     */
    public function addItem($newItem)
    {
        $this->items[] = $newItem;

        return $this;
    }
}
