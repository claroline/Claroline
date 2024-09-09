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

use Symfony\Contracts\EventDispatcher\Event;

class GenericDataEvent extends Event
{
    private mixed $data;
    private array $response = [];

    public function __construct(mixed $data = null)
    {
        $this->data = $data;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getResponse(): array
    {
        return $this->response;
    }

    public function setResponse(?array $response = null): void
    {
        if (!empty($response)) {
            $this->response = array_merge_recursive($this->response, $response);
        }
    }
}
