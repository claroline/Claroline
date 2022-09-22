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
use Claroline\CoreBundle\Messenger\Message\CopyWorkspace;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CopyWorkspaceHandler implements MessageHandlerInterface
{
    /** @var Crud */
    private $crud;

    public function __construct(Crud $crud)
    {
        $this->crud = $crud;
    }

    public function __invoke(CopyWorkspace $copyWorkspace)
    {
        $workspace = $this->crud->get(Workspace::class, $copyWorkspace->getWorkspaceId());
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
