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

use Claroline\AppBundle\Messenger\Message\AsyncLowMessageInterface;

/**
 * Import a workspace from a Claroline archive.
 */
class ImportWorkspace implements AsyncLowMessageInterface
{
    public function __construct(
        private readonly string $archivePath,
        private readonly ?string $name = null,
        private readonly ?string $code = null
    ) {
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
