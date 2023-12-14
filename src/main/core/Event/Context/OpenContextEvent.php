<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Context;

/**
 * Event fired when a context is opened.
 */
final class OpenContextEvent extends AbstractContextEvent
{
    private array $response = [];

    /**
     * Sets response data to return in the api.
     * NB. It MUST contain serialized structures.
     */
    public function addResponse(array $responseData): void
    {
        $this->response = array_merge($responseData, $this->response);
    }

    public function getResponse(): array
    {
        return $this->response;
    }
}
