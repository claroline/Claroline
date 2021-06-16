<?php

namespace Claroline\SamlBundle\Security;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Security\Authentication\Authenticator;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use LightSaml\Model\Protocol\Response;
use LightSaml\SpBundle\Security\User\UserCreatorInterface;
use LightSaml\SpBundle\Security\User\UsernameMapperInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserCreator implements UserCreatorInterface
{
    /** @var Authenticator */
    private $authenticator;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var UsernameMapperInterface */
    private $usernameMapper;
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;
    /** @var PlatformConfigurationHandler */
    private $config;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        Authenticator $authenticator,
        UsernameMapperInterface $usernameMapper,
        PlatformConfigurationHandler $config,
        ObjectManager $om,
        Crud $crud
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticator = $authenticator;
        $this->usernameMapper = $usernameMapper;
        $this->om = $om;
        $this->crud = $crud;
        $this->config = $config;
    }

    /**
     * @return UserInterface|null
     */
    public function createUser(Response $response)
    {
        // get the config of the idp currently used
        $idpConfig = [];
        $issuer = $response->getIssuer()->getValue();
        if (!empty($issuer)) {
            $idp = $this->config->get('saml.idp');
            if (!empty($idp[$issuer])) {
                $idpConfig = $idp[$issuer];
            }
        }

        $username = $this->usernameMapper->getUsername($response);

        $email = $response
            ->getFirstAssertion()
            ->getFirstAttributeStatement()
            ->getFirstAttributeByName('iam-email')
            ->getFirstAttributeValue();

        $firstName = $response
            ->getFirstAssertion()
            ->getFirstAttributeStatement()
            ->getFirstAttributeByName('iam-firstname')
            ->getFirstAttributeValue();

        $lastName = $response
            ->getFirstAssertion()
            ->getFirstAttributeStatement()
            ->getFirstAttributeByName('iam-lastname')
            ->getFirstAttributeValue();

        // FIXME : I need a token to be able to start user creation process
        // but it's not already filled and I can't rewrite the whole process to test token existence.
        $this->setToken();

        // attach user to the defined organization
        $organization = null;
        if (!empty($idpConfig['organization'])) {
            $organization = ['id' => $idpConfig['organization']];
        }

        /** @var User $user */
        $user = $this->crud->create(User::class, [
            'username' => $username,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'plainPassword' => uniqid(), // I cannot create a user without pass
            'meta' => [
                'mailValidated' => true, // because we receive a trusted email
            ],
            'mainOrganization' => $organization,
            'restrictions' => [
                'disabled' => false,
            ],
        ], [Crud::THROW_EXCEPTION, Options::NO_EMAIL]);

        if (!empty($idpConfig['groups'])) {
            $groups = [];
            foreach ($idpConfig['groups'] as $groupId) {
                $group = $this->om->getRepository(Group::class)->findOneBy(['uuid' => $groupId]);
                if (!empty($group)) {
                    $groups[] = $group;
                }
            }

            if (!empty($groups)) {
                $this->crud->patch($user, 'group', 'add', $groups, [Crud::THROW_EXCEPTION, Options::NO_EMAIL]);
            }
        }

        $this->tokenStorage->setToken(null);

        return $user;
    }

    private function setToken()
    {
        if (null !== $this->tokenStorage->getToken()) {
            // user is already authenticated, there is nothing to do.
            return;
        }

        // creates an anonymous token with a dedicated role.
        $this->authenticator->createAnonymousToken();
    }
}
