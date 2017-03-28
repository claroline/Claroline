<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 3/22/17
 */

namespace Claroline\CasBundle\Listener;

use Claroline\CoreBundle\Listener\AuthenticationSuccessListener;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @DI\Service("claroline.cas.authentication_handler")
 */
class CasAuthenticationSuccessListener extends AuthenticationSuccessListener
{
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $request->getSession()->set('LOGGED_VIA_CAS', true);

        return parent::onAuthenticationSuccess($request, $token);
    }
}
