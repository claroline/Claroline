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
 * Create a workspace.
 */
class CreateWorkspace implements AsyncLowMessageInterface
{
    public function __construct(
        private readonly array $data,
        private readonly ?array $options = []
    ) {
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }
}
