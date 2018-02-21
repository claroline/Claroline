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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\SecurityToken;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.security_token_manager")
 */
class SecurityTokenManager
{
    private $om;
    private $securityTokenRepo;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->securityTokenRepo =
            $om->getRepository('ClarolineCoreBundle:SecurityToken');
    }

    public function persistSecurityToken(SecurityToken $securityToken)
    {
        $this->om->persist($securityToken);
        $this->om->flush();
    }

    public function deleteSecurityToken(SecurityToken $securityToken)
    {
        $this->om->remove($securityToken);
        $this->om->flush();
    }

    /********************************************
     *  SecurityTokenRepository access methods  *
     ********************************************/

    public function getAllTokens($order = 'clientName', $direction = 'ASC')
    {
        return $this->securityTokenRepo->findAllTokens($order, $direction);
    }

    public function getSecurityTokenByClientNameAndTokenAndIp(
        $clientName,
        $token,
        $ip
    ) {
        return $this->securityTokenRepo
            ->findSecurityTokenByClientNameAndTokenAndIp($clientName, $token, $ip);
    }
}
