<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Tool;

use Claroline\AppBundle\Event\DataConveyorEventInterface;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

class ConfigureToolEvent extends AbstractToolEvent implements DataConveyorEventInterface
{
    /** @var array */
    private $parameters;
    /** @var array */
    private $data = [];
    /** @var bool */
    private $isPopulated = false;

    public function __construct(string $toolName, string $context, ?Workspace $workspace = null, ?array $parameters = [])
    {
        parent::__construct($toolName, $context, $workspace);

        $this->parameters = $parameters;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Sets data to return in the api.
     * NB. It MUST contain serialized structures.
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
