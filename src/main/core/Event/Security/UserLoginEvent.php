<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Security;

use Claroline\CoreBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserLoginEvent extends Event
{
    /**
     * Data which will be sent to the client after a successful login.
     * Example. ThemeBundle will return the user appearance options.
     */
    private array $responseData = [];

    public function __construct(
        private readonly User $user
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Sets response data to return in the api.
     * NB. It MUST contain serialized structures.
     */
    public function addResponse(array $responseData): void
    {
        $this->responseData = array_merge($responseData, $this->responseData);
    }

    public function getResponse(): array
    {
        return $this->responseData;
    }
}
