<?php

namespace Icap\OAuthBundle\Security\Hwi;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth2ResourceOwner as HWIResourceOwner;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenericResourceOwner extends HWIResourceOwner
{
    protected $paths = [
        'identifier' => 'id',
        'email' => 'email',
        'realname' => 'fullname',
        'nickname' => 'username',
        'firstname' => 'firstname',
        'lastname' => 'lastname',
    ];

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'access_token_url' => '',
            'infos_url' => '',
            'paths' => [],
        ]);
    }
}
