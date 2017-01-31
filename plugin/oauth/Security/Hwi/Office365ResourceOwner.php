<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 12/1/16
 */

namespace Icap\OAuthBundle\Security\Hwi;

use Buzz\Client\ClientInterface as HttpClientInterface;
use HWI\Bundle\OAuthBundle\OAuth\RequestDataStorageInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth2ResourceOwner;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Http\HttpUtils;

class Office365ResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * @var array
     */
    protected $paths = [
        'identifier' => 'id',
        'email' => 'mail',
        'realname' => 'displayName',
        'nickname' => 'userPrincipalName',
        'firstname' => 'givenName',
        'lastname' => 'surname',
    ];

    /**
     * @var string
     */
    protected $resource;

    /**
     * @var string
     */
    protected $infosUrl;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        HttpClientInterface $httpClient,
        HttpUtils $httpUtils,
        array $options,
        $name,
        RequestDataStorageInterface $storage,
        $tenantDomain = null,
        $apiVersion = null
    ) {
        // By default to microsoft graph, not AD graph
        $this->resource = 'https://graph.microsoft.com';
        $this->infosUrl = $this->resource.'/v1.0/me';
        // If domain is set
        if (!empty($tenantDomain)) {
            $this->paths['identifier'] = 'objectId';
            $this->resource = 'https://graph.windows.net';
            $this->infosUrl = "{$this->resource}/{$tenantDomain}/me".
                "?api-version={$this->getApiVersion($apiVersion)}";
        }

        parent::__construct($httpClient, $httpUtils, $options, $name, $storage);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://login.microsoftonline.com/common/oauth2/authorize',
            'access_token_url' => 'https://login.microsoftonline.com/common/oauth2/token',
            'revoke_token_url' => 'https://login.microsoftonline.com/common/oauth2/logout',
            'infos_url' => $this->infosUrl,
            'force_login' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = [])
    {
        $extraParameters = [
            'resource' => $this->resource,
        ];

        return parent::getAccessToken($request, $redirectUri, $extraParameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = [])
    {
        $extraParameters = [
            'resource' => $this->resource,
        ];

        return parent::getAuthorizationUrl($redirectUri, $extraParameters);
    }

    /**
     * Logouts User from resource Owner.
     *
     * @param $redirectUrl
     *
     * @return RedirectResponse
     */
    public function logout($redirectUrl)
    {
        if (!empty($this->options['revoke_token_url']) && $this->options['force_login'] === true) {
            $redirectUrl = $this->normalizeUrl(
                $this->options['revoke_token_url'],
                ['post_logout_redirect_uri' => $redirectUrl]
            );
        }

        return new RedirectResponse($redirectUrl);
    }

    private function getApiVersion($apiVersion)
    {
        return !empty($apiVersion) ? $apiVersion : '2013-11-08';
    }
}
