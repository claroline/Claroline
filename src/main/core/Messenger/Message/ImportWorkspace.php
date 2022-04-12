<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncMessageInterface;

/**
 * Import a workspace from a Claroline archive.
 */
class ImportWorkspace implements AsyncMessageInterface
{
    /** @var string */
    private $archivePath;
    /** @var string */
    private $name;
    /** @var string */
    private $code;

    public function __construct(string $archivePath, ?string $name = null, ?string $code = null)
    {
        $this->archivePath = $archivePath;
        $this->name = $name;
        $this->code = $code;
    }

    public function getArchivePath(): string
    {
        return $this->archivePath;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }
}
