<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Event\StrictDispatcher;
use FOS\OAuthServerBundle\Entity\ClientManager;
use Claroline\CoreBundle\Entity\Oauth\Client;

/**
 * @DI\Service("claroline.manager.oauth_manager", parent="fos_oauth_server.client_manager.default")
 */
class OauthManager extends ClientManager
{
    public function findAllClients()
    {
        return $this->repository->findAll();
    }

    public function connect(Client $client)
    {

    }
}
