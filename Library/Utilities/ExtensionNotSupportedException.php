<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Utilities;

class ExtensionNotSupportedException extends \Exception
{
    private $extension;

    public function setExtension($ext)
    {
        $this->extension = $ext;
    }

    public function getExtension()
    {
        return $this->extension;
    }
}
