<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;

trait ResourceNotifiableTrait
{
    use ResourceNotifiableTrait;

    /**
     * Get if event is allowed to create notification or not.
     *
     * @return bool
     */
    public function isAllowedToNotify()
    {
        if (!$this->node->isPublished()) {
            return false;
        }

        return $this->showNotification($this->node, $this->node->isPublished());
    }

    private function showNotification(ResourceNode $node, $published)
    {
        $parent = $node->getParent();

        if ($parent && $published) {
            $published = $this->showNotification($parent, $published);
        }

        return $parent ? $parent->isPublished() : true;
    }
}
