<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Exception;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ResourceAccessException extends AccessDeniedException
{
    private $nodes;
    private $error;

    public function __construct($error, array $nodes)
    {
        $this->error = $error;
        $this->nodes = $nodes;
    }

    public function getNodes()
    {
        return $this->nodes;
    }
}
