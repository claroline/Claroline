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

namespace Icap\OAuthBundle\Security\Hwi;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\WindowsLiveResourceOwner as HWIWindowsLiveResourceOwner;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    public function logout($redirectUrl)
    {
        if (!empty($this->options['revoke_token_url']) && $this->options['force_login'] === true) {
            $redirectUrl = $this->normalizeUrl(
                $this->options['revoke_token_url'],
                [
                    'client_id' => $this->options['client_id'],
                    'redirect_uri' => $redirectUrl,
                ]
            );
        }

        return new RedirectResponse($redirectUrl);
    }
}
