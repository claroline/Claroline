<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Messenger;

use Claroline\AppBundle\API\Crud;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Messenger\Message\CreateWorkspace;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CreateWorkspaceHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly Crud $crud
    ) {
    }

    public function __invoke(CreateWorkspace $createWorkspace): void
    {
        try {
            $this->crud->create(Workspace::class, $createWorkspace->getData(), $createWorkspace->getOptions());
        } catch (\Exception $e) {
            // we don't want to retry action and create more broken data in the DB.
            throw new UnrecoverableMessageHandlingException($e->getMessage(), $e->getCode());
        }
    }
}
