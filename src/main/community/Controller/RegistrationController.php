<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Security\Authentication\Authenticator;
use Claroline\CommunityBundle\Serializer\ProfileSerializer;
use Claroline\CoreBundle\Configuration\PlatformDefaults;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\PrivacyBundle\Manager\PrivacyManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller for user self-registration. Access to this functionality requires
 * that the user is anonymous and the self-registration is allowed by the
 * platform configuration.
 */
#[Route(path: '/user/registration')]
class RegistrationController
{
    use RequestDecoderTrait;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly PlatformConfigurationHandler $config,
        private readonly Crud $crud,
        private readonly SerializerProvider $serializer,
        private readonly ProfileSerializer $profileSerializer,
        private readonly PrivacyManager $privacyManager,
        private readonly Authenticator $authenticator
    ) {
    }

    #[Route(path: '/', name: 'apiv2_user_register', methods: ['POST'])]
    public function registerAction(Request $request): Response
    {
        $this->checkAccess();

        /** @var array|User $user */
        $user = $this->crud->create(User::class, $this->decodeRequest($request), [
            // maybe move these options in another class
            Options::REGISTRATION,
            Options::VALIDATE_FACET,
        ]);

        $validation = $this->config->getParameter('registration.validation');
        // auto log user if option is set and account doesn't need to be validated
        if (PlatformDefaults::REGISTRATION_MAIL_VALIDATION_FULL !== $validation) {
            return $this->authenticator->login($user, $request);
        }

        return new JsonResponse($this->serializer->serialize($user), 204);
    }

    /**
     * Fetches data for self-registration form.
     */
    #[Route(path: '/', name: 'apiv2_user_initialize_registration', methods: ['GET'])]
    public function initializeAction(Request $request): JsonResponse
    {
        $this->checkAccess();
        $terms = null;
        if ($this->privacyManager->getTosEnabled($request->getLocale())) {
            $terms = $this->privacyManager->getTosTemplate($request->getLocale());
        }

        return new JsonResponse([
            'facets' => $this->profileSerializer->serialize([Options::REGISTRATION]),
            'termOfService' => $terms,
            'options' => [
                'validation' => $this->config->getParameter('registration.validation'),
                'organizationSelection' => $this->config->getParameter('registration.organization_selection'),
            ],
        ]);
    }

    /**
     * Checks if a user is allowed to register.
     * ie: if the self registration is disabled, he can't.
     *
     * @throws AccessDeniedException
     */
    private function checkAccess(): void
    {
        if (!$this->config->getParameter('registration.self') || $this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }
    }
}
