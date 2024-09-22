<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Platform;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\ConnectionMessage\ConnectionMessage;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\ConnectionMessageManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/connection_message', name: 'apiv2_connection_message_')]
class ConnectionMessageController extends AbstractCrudController
{
    public function __construct(
        private readonly ConnectionMessageManager $manager
    ) {
    }

    public static function getName(): string
    {
        return 'connection_message';
    }

    public static function getClass(): string
    {
        return ConnectionMessage::class;
    }

    /**
     * Discards a message for the next login.
     */
    #[Route(path: '/{id}/discard', name: 'discard', methods: 'PUT')]
    public function discardAction(#[MapEntity(class: 'Claroline\CoreBundle\Entity\ConnectionMessage\ConnectionMessage', mapping: ['id' => 'uuid'])]
    ConnectionMessage $message, #[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(null, 204);
        }

        $this->manager->discard($message, $user);

        return new JsonResponse(null, 204);
    }
}
