<?php

namespace Claroline\AuthenticationBundle\Manager;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Component\OAuth\OAuth2Interface;
use Claroline\AuthenticationBundle\Component\OAuth\OAuth2Provider;
use Claroline\AuthenticationBundle\Entity\OAuthClient;
use Claroline\CoreBundle\Entity\User;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class OAuthManager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        LoggerInterface $logger,
        private readonly RouterInterface $router,
        private readonly OAuth2Provider $oauthProvider,
        private readonly ObjectManager $om,
        private readonly Crud $crud,
    ) {
        $this->logger = $logger;
    }

    /**
     * Get the list of all implemented service providers for OAuth (e.g., generic, GitHub, Azure, ...).
     */
    public function getAvailableProviders(): array
    {
        return array_map(function (OAuth2Interface $oauth) {
            return [
                'icon' => $oauth::getIcon(),
                'name' => $oauth::getName(),
                'defaultMapping' => $oauth->getDefaultMapping(),
            ];
        }, $this->oauthProvider->getAvailableOAuths());
    }

    /**
     * Get the list of all OAuth clients configured for the platform.
     */
    public function getAvailableClients(): array
    {
        return $this->om->getRepository(OAuthClient::class)->findAll();
    }

    /**
     * Get the list of SSO buttons to display on the login page.
     */
    public function getDisplayedButtons(): array
    {
        $displayedClients = $this->om->getRepository(OAuthClient::class)->findBy([
            'disabled' => false,
            'buttonDisplayed' => true,
        ]);

        return array_map(function (OAuthClient $oauthClient) {
            return [
                'service' => 'oauth2',
                'clientId' => $oauthClient->getUuid(),
                'icon' => 'fa-'.$oauthClient->getButtonIcon(),
                'label' => $oauthClient->getButtonLabel() ?: $oauthClient->getName(),
                'confirm' => $oauthClient->getButtonConfirm(),
            ];
        }, $displayedClients);
    }

    public function startAuthentication(Request $request, OAuthClient $client): string
    {
        $provider = $this->getProvider($client);

        // store some values in session to retrieve them when the identity provider calls our redirectUri
        $session = $request->getSession();

        // Fetch the authorization URL from the provider; this returns the
        // urlAuthorize option and generates and applies any necessary parameters
        // (e.g., state).
        $authorizationUrl = $provider->getAuthorizationUrl([
            'redirectUri' => $this->router->generate('claro_security_login_check_oauth2', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        // store the local id of the oauth client used for the authentication
        $session->set('oauth2client', $client->getUuid());
        // store state in session to mitigate CSRF attack later
        $session->set('oauth2state', $provider->getState());
        // get the PKCE code generated for you and store it to the session.
        $session->set('oauth2pkceCode', $provider->getPkceCode());

        if (!empty($request->get('redirectPath')) && '#/login' !== $request->get('redirectPath')) {
            // store it in session before leaving claroline for authentication
            // this will allow use to redirect to the correct ui fragment when going back to claroline
            $session->set('redirectPath', $request->get('redirectPath'));
        }

        return $authorizationUrl;
    }

    public function authenticate(Request $request): Passport
    {
        $this->validateRequest($request);

        // get the oauth client configuration
        /** @var OAuthClient $client */
        $client = $this->om->getRepository(OAuthClient::class)->findOneBy(['uuid' => $request->getSession()->get('oauth2client')]);
        if (empty($client)) {
            throw new CustomUserMessageAuthenticationException('Client does not exist.');
        }

        $provider = $this->getProvider($client);
        $provider->setPkceCode($request->getSession()->get('oauth2pkceCode'));

        // Try to get an access token (using the authorization code grant)
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $request->get('code'),
        ]);
        // We got an access token, let's now get the user's details
        $resourceOwner = $provider->getResourceOwner($token);

        $user = $this->getUserFromResourceOwner($resourceOwner, $client);

        $this->om->startFlushSuite();
        if (!$user) {
            if (!$client->getCreateOnLogin()) {
                throw new CustomUserMessageAuthenticationException('User not found.');
            }

            try {
                $user = $this->createUserFromResourceOwner($resourceOwner, $client);
            } catch (\Exception $e) {
                throw new CustomUserMessageAuthenticationException('Cannot create user.');
            }
        }

        if ($user->isDisabled()) {
            if (!$client->getReactivateOnLogin()) {
                throw new CustomUserMessageAuthenticationException('User is disabled.');
            }
            $user->setDisabled(false);
            $this->om->persist($user);
        }

        if (0 !== $client->getOrganizations()->count()) {
            // add the user to the defined organizations
            $this->crud->patch($user, 'organization', Crud::COLLECTION_ADD, $client->getOrganizations()->toArray());
        }

        $this->om->endFlushSuite();

        $this->logger->debug(sprintf('Authenticated with %s', $client->getServiceProvider()));

        // we don't need to pass the `$userLoader` param to UserBadge because it uses the default UserProvider
        // we do it for tests isolation
        return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier(), function (string $userIdentifier): ?UserInterface {
            return $this->om->getRepository(User::class)->loadUserByIdentifier($userIdentifier);
        }));
    }

    private function getUserFromResourceOwner(ResourceOwnerInterface $resourceOwner, OAuthClient $client): ?User
    {
        $normalizedData = $this->extractResourceOwnerData($resourceOwner, $client);

        $user = null;
        try {
            $user = $this->om->getRepository(User::class)->loadUserByIdentifier($normalizedData['email']);
        } catch (\Exception $e) {
        }

        return $user;
    }

    private function createUserFromResourceOwner(ResourceOwnerInterface $resourceOwner, OAuthClient $client): User
    {
        $normalizedData = $this->extractResourceOwnerData($resourceOwner, $client);

        /** @var User $user */
        $user = $this->crud->create(User::class, [
            'username' => $normalizedData['username'],
            'firstName' => $normalizedData['firstName'],
            'lastName' => $normalizedData['lastName'],
            'email' => $normalizedData['email'],
            'plainPassword' => uniqid(), // we cannot create a user without a password
            'meta' => [
                'mailValidated' => true, // because we receive a trusted email
            ],
            'restrictions' => [
                'disabled' => false,
            ],
        ], [Crud::NO_PERMISSIONS, Options::NO_EMAIL]);

        return $user;
    }

    /**
     * Check if we can authenticate the request.
     */
    private function validateRequest(Request $request): void
    {
        $session = $request->getSession();

        // validate request before trying to authenticate the user
        if (empty($session->get('oauth2client'))) {
            throw new BadRequestHttpException('No authentication process started.');
        }

        // check given state against previously stored one to mitigate CSRF attack
        if (empty($request->get('state')) || $request->get('state') !== $session->get('oauth2state')) {
            $session->set('oauth2state', null);

            throw new BadRequestHttpException('Invalid state.');
        }

        if (empty($request->get('code'))) {
            throw new BadRequestHttpException('Missing validation code.');
        }
    }

    /**
     * Get the authentication provider for the defined client.
     */
    private function getProvider(OAuthClient $client): AbstractProvider
    {
        $oauthComponent = $this->oauthProvider->getComponent($client->getServiceProvider());

        return $oauthComponent->getProvider($client);
    }

    private function getFieldsMapping(OAuthClient $client): array
    {
        $oauthComponent = $this->oauthProvider->getComponent($client->getServiceProvider());

        $defaultMapping = $oauthComponent->getDefaultMapping();

        return array_merge($defaultMapping, $client->getFieldsMapping() ?? []);
    }

    private function extractResourceOwnerData(ResourceOwnerInterface $resourceOwner, OAuthClient $client): array
    {
        $fieldsMapping = $this->getFieldsMapping($client);
        $resourceOwnerData = $resourceOwner->toArray();

        $normalizedData = [];
        foreach ($fieldsMapping as $localKey => $ownerKey) {
            $normalizedData[$localKey] = $resourceOwnerData[$ownerKey];
        }

        if (empty($normalizedData['firstName']) || empty($normalizedData['lastName'])) {
            // not all oauth providers will give us a firstName and lastName which are required in our model
            // try to compute firstName and lastName from the user full name provided by the oauth
            // this will be far from perfect because there is no normalization on this
            // Dumb method: we will split the name on whitespace and get the first part as firstName and last part as lastName (yes it sucks)
            $nameParts = explode(' ', $normalizedData['name']);
            if (!empty($nameParts)) {
                if (empty($normalizedData['firstName'])) {
                    $normalizedData['firstName'] = $nameParts[0];
                }

                if (empty($normalizedData['lastName'])) {
                    $normalizedData['lastName'] = $nameParts[count($nameParts) - 1];
                }
            }
        }

        return $normalizedData;
    }
}
