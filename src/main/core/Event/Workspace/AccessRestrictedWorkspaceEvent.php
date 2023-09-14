<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Workspace;

use Claroline\CoreBundle\Entity\Workspace\Workspace;

/**
 * Event dispatched when a workspace is opened but the user can not access it.
 */
class AccessRestrictedWorkspaceEvent extends AbstractWorkspaceEvent
{
    private array $errors;

    public function __construct(Workspace $workspace, ?array $errors = [])
    {
        parent::__construct($workspace);

        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError(string $errorKey, $errorData): void
    {
        if (array_key_exists($errorKey, $this->errors)) {
            throw new \LogicException(sprintf('Access restrictions already contains a key %s. You can not override it. Please use a different $errorKey for your error', $errorKey));
        }

        $this->errors[$errorKey] = $errorData;
    }
}
