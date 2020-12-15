<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Log\Connection;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Log\Connection\LogConnectPlatformRepository")
 * @ORM\Table(name="claro_log_connect_platform")
 */
class LogConnectPlatform extends AbstractLogConnect
{
}
