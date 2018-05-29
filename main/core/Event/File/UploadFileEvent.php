<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\File;

use Claroline\CoreBundle\Entity\File\PublicFile;
use Symfony\Component\EventDispatcher\Event;

/**
 * Upload File event.
 */
class UploadFileEvent extends Event
{
    /** @var PublicFile */
    private $file;

    /**
     * @param PublicFile $file
     **/
    public function __construct(PublicFile $file)
    {
        $this->file = $file;
    }

    /**
     * @return PublicFile
     */
    public function getFile()
    {
        return $this->file;
    }
}
