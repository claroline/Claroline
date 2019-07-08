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

use Claroline\AppBundle\Event\DataConveyorEventInterface;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\EventDispatcher\Event;

class DisplayToolEvent extends Event implements DataConveyorEventInterface
{
    private $workspace;
    private $content;

    /** @var array */
    private $data = [];

    /** @var bool */
    private $isPopulated = false;

    public function __construct(Workspace $workspace = null)
    {
        $this->workspace = $workspace;
    }

    public function setContent($content)
    {
        $this->isPopulated = true;
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * Sets data to return in the api.
     * NB. It MUST contain serialized structures.
     *
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
        $this->isPopulated = true;
    }

    public function getData()
    {
        return $this->data;
    }

    public function isPopulated()
    {
        return $this->isPopulated;
    }
}
