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

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Messenger\Message\ImportWorkspace;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
class ImportWorkspaceHandler
{
    public function __construct(
        private readonly WorkspaceManager $manager
    ) {
    }

    public function __invoke(ImportWorkspace $importWorkspace): void
    {
        $newWorkspace = new Workspace();

        if (!empty($importWorkspace->getName())) {
            $newWorkspace->setName($importWorkspace->getName());
        }
        if (!empty($importWorkspace->getCode())) {
            $newWorkspace->setCode($importWorkspace->getCode());
        }

        $filesystem = new Filesystem();
        try {
            $this->manager->import($importWorkspace->getArchivePath(), $newWorkspace);
            $filesystem->remove($importWorkspace->getArchivePath());
        } catch (\Exception $e) {
            $filesystem->remove($importWorkspace->getArchivePath());

            // we don't want to retry action and create more broken data in the DB.
            throw new UnrecoverableMessageHandlingException($e->getMessage(), $e->getCode());
        }
    }
}
