<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 12/6/16
 */

namespace Icap\OAuthBundle\Security\Hwi;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\FacebookResourceOwner as HWIFacebookResourceOwner;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class FacebookResourceOwner extends HWIFacebookResourceOwner
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        // Symfony <2.6 BC
        if (method_exists($resolver, 'setDefined')) {
            $resolver->setAllowedValues('auth_type', ['reauthenticate', null]);
        } else {
            $resolver->setAllowedValues([
                'auth_type' => ['reauthenticate', null],
            ]);
        }
    }

    public function revokeToken($token)
    {
        if (empty($this->options['revoke_token_url'])) {
            throw new AuthenticationException('OAuth error: "Method unsupported."');
        }

        return parent::revokeToken($token);
    }
}
