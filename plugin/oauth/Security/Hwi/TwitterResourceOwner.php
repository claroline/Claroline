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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\TwitterResourceOwner as HWITwitterResourceOwner;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TwitterResourceOwner extends HWITwitterResourceOwner
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
           'force_login' => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = [])
    {
        $token = $this->getRequestToken($redirectUri, $extraParameters);

        return $this->normalizeUrl(
            $this->options['authorization_url'],
            [
                'oauth_token' => $token['oauth_token'],
                'force_login' => $this->options['force_login'],
            ]
        );
    }
}
