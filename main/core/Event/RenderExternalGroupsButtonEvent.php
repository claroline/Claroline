<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 5/24/17
 */

namespace Claroline\CoreBundle\Event;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\EventDispatcher\Event;

class RenderExternalGroupsButtonEvent extends Event
{
    private $content;
    private $workspace;

    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
        $this->content = '';
    }

    public function addContent($content)
    {
        $this->content .= $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }
}
