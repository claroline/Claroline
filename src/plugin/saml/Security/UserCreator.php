<?php

namespace Claroline\SamlBundle\Security;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AuthenticationBundle\Security\Authentication\Authenticator;
use Claroline\CoreBundle\Entity\User;
use Claroline\SamlBundle\Manager\IdpManager;
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
    /** @var Crud */
    private $crud;
    /** @var IdpManager */
    private $idpManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        Authenticator $authenticator,
        UsernameMapperInterface $usernameMapper,
        Crud $crud,
        IdpManager $idpManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticator = $authenticator;
        $this->usernameMapper = $usernameMapper;
        $this->crud = $crud;
        $this->idpManager = $idpManager;
    }

    /**
     * @return UserInterface|null
     */
    public function createUser(Response $response)
    {
        $issuer = $response->getIssuer()->getValue();

        $username = $this->usernameMapper->getUsername($response);
        $fieldMapping = $this->idpManager->getFieldMapping($issuer);

        $email = $response
            ->getFirstAssertion()
            ->getFirstAttributeStatement()
            ->getFirstAttributeByName($fieldMapping['email'])
            ->getFirstAttributeValue();

        $firstName = $response
            ->getFirstAssertion()
            ->getFirstAttributeStatement()
            ->getFirstAttributeByName($fieldMapping['firstName'])
            ->getFirstAttributeValue();

        $lastName = $response
            ->getFirstAssertion()
            ->getFirstAttributeStatement()
            ->getFirstAttributeByName($fieldMapping['lastName'])
            ->getFirstAttributeValue();

        // FIXME : I need a token to be able to start user creation process
        // but it's not already filled and I can't rewrite the whole process to test token existence.
        $this->setToken();

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
            'restrictions' => [
                'disabled' => false,
            ],
        ], [Crud::THROW_EXCEPTION, Crud::NO_PERMISSIONS, Options::NO_EMAIL]);

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
