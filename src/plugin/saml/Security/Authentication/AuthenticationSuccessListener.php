<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SamlBundle\Security\Authentication;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Listener\AuthenticationSuccessListener as BaseAuthenticationSuccessListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthenticationSuccessListener extends BaseAuthenticationSuccessListener
{
    /** @var ObjectManager */
    private $om;

    public function setObjectManager(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user->isEnabled()) {
            $user->enable();
            $this->om->persist($user); // no need to flush user it will be done later
        }

        return parent::onAuthenticationSuccess($request, $token);
    }
}
