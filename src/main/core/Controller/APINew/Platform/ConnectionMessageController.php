<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Platform;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\ConnectionMessage\ConnectionMessage;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\ConnectionMessageManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/connectionmessage")
 */
class ConnectionMessageController extends AbstractCrudController
{
    /** @var ConnectionMessageManager */
    private $manager;

    public function __construct(ConnectionMessageManager $manager)
    {
        $this->manager = $manager;
    }

    public function getName(): string
    {
        return 'connectionmessage';
    }

    public function getClass(): string
    {
        return ConnectionMessage::class;
    }

    public function getIgnore(): array
    {
        return ['exist', 'copyBulk', 'csv', 'find'];
    }

    /**
     * Discards a message for the next login.
     *
     * @Route("/{id}/discard", name="apiv2_connection_message_discard", methods="PUT")
     * @EXT\ParamConverter(
     *     "message",
     *     class="Claroline\CoreBundle\Entity\ConnectionMessage\ConnectionMessage",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function discardAction(ConnectionMessage $message, User $user): JsonResponse
    {
        $this->manager->discard($message, $user);

        return new JsonResponse(null, 204);
    }
}
