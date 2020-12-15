<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 12/7/16
 */

namespace Claroline\AuthenticationBundle\Security\Oauth\Hwi;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\WindowsLiveResourceOwner as HWIWindowsLiveResourceOwner;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WindowsLiveResourceOwner extends HWIWindowsLiveResourceOwner
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'revoke_token_url' => 'https://login.live.com/oauth20_logout.srf',
            'force_login' => false,
        ]);
    }

    public function revokeToken($token)
    {
        if (!empty($this->options['revoke_token_url']) && true === $this->options['force_login']) {
            $parameters = [
                'client_id' => $this->options['client_id'],
                'client_secret' => $this->options['client_secret'],
            ];

            $response = $this->httpRequest($this->normalizeUrl($this->options['revoke_token_url'], ['client_id' => $this->options['client_id']]), $parameters);

            return 200 === $response->getStatusCode();
        }

        return false;
    }
}
