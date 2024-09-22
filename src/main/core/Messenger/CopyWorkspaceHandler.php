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
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Messenger\Message\CopyWorkspace;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
class CopyWorkspaceHandler
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly Crud $crud
    ) {
    }

    public function __invoke(CopyWorkspace $copyWorkspace): void
    {
        $workspace = $this->om->getRepository(Workspace::class)->find($copyWorkspace->getWorkspaceId());
        if (empty($workspace)) {
            return;
        }

        try {
            $this->crud->copy($workspace, $copyWorkspace->getOptions());
        } catch (\Exception $e) {
            // we don't want to retry action and create more broken data in the DB.
            throw new UnrecoverableMessageHandlingException($e->getMessage(), $e->getCode());
        }
    }
}
