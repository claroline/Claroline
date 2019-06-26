<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\ConnectionMessage;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\ConnectionMessage\ConnectionMessage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * @EXT\Route("/connectionmessage")
 */
class ConnectionMessageController extends AbstractCrudController
{
    public function getName()
    {
        return 'connectionmessage';
    }

    public function getClass()
    {
        return ConnectionMessage::class;
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'doc', 'find'];
    }
}
