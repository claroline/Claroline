<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\User;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Manager\TermsOfServiceManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Security\Authentication\Authenticator;
use Claroline\CoreBundle\API\Serializer\User\ProfileSerializer;
use Claroline\CoreBundle\Configuration\PlatformDefaults;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller for user self-registration. Access to this functionality requires
 * that the user is anonymous and the self-registration is allowed by the
 * platform configuration.
 *
 * @Route("/user/registration")
 */
class RegistrationController
{
    use RequestDecoderTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var Crud */
    private $crud;
    /** @var SerializerProvider */
    private $serializer;
    /** @var ProfileSerializer */
    private $profileSerializer;
    /** @var TermsOfServiceManager */
    private $termsOfServiceManager;
    /** @var Authenticator */
    private $authenticator;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        PlatformConfigurationHandler $config,
        Crud $crud,
        SerializerProvider $serializer,
        ProfileSerializer $profileSerializer,
        TermsOfServiceManager $termsOfServiceManager,
        Authenticator $authenticator
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->config = $config;
        $this->crud = $crud;
        $this->serializer = $serializer;
        $this->profileSerializer = $profileSerializer;
        $this->authenticator = $authenticator;
        $this->termsOfServiceManager = $termsOfServiceManager;
    }

    /**
     * @Route("/", name="apiv2_user_register", methods={"POST"})
     *
     * @return Response
     */
    public function registerAction(Request $request)
    {
        $this->checkAccess();

        $data = $this->decodeRequest($request);

        $organizationRepository = $this->om->getRepository(Organization::class);

        $organization = null;
        $autoOrganization = 'create' === $this->config->getParameter('registration.organization_selection');
        // step one: creation the organization if it's here. If it exists, we fetch it.
        if ($autoOrganization) {
            // try to find orga first
            if (isset($data['mainOrganization'])) {
                if (isset($data['mainOrganization']['vat']) && null !== $data['mainOrganization']['vat']) {
                    $organization = $organizationRepository
                        ->findOneBy(['vat' => $data['mainOrganization']['vat']]);
                } else {
                    $organization = $organizationRepository
                        ->findOneBy(['code' => $data['mainOrganization']['code']]);
                }
            }

            if (!$organization && isset($data['mainOrganization'])) {
                $organization = $this->crud->create(Organization::class, $data['mainOrganization']);
            }

            // error handling
            if (is_array($organization)) {
                return new JsonResponse($organization, 422);
            }
        }

        /** @var array|User $user */
        $user = $this->crud->create(User::class, $this->decodeRequest($request), [
            //maybe move these options in an other class
            Options::ADD_NOTIFICATIONS,
            Options::WORKSPACE_VALIDATE_ROLES,
            Options::VALIDATE_FACET,
        ]);

        // error handling
        if (is_array($user)) {
            return new JsonResponse($user, 422);
        }

        if ($organization) {
            $this->crud->replace($user, 'mainOrganization', $organization);
        }

        $selfLog = $this->config->getParameter('registration.auto_logging');
        $validation = $this->config->getParameter('registration.validation');
        // auto log user if option is set and account doesn't need to be validated
        if ($selfLog && PlatformDefaults::REGISTRATION_MAIL_VALIDATION_FULL !== $validation) {
            return $this->authenticator->login($user, $request);
        }

        return new JsonResponse($this->serializer->serialize($user), 204);
    }

    /**
     * Fetches data for self-registration form.
     *
     * @Route("/", name="apiv2_user_initialize_registration", methods={"GET"})
     */
    public function initializeAction(Request $request): JsonResponse
    {
        $this->checkAccess();

        $terms = null;
        if ($this->termsOfServiceManager->isActive()) {
            $terms = $this->termsOfServiceManager->getLocalizedTermsOfService($request->getLocale());
        }

        return new JsonResponse([
            'facets' => $this->profileSerializer->serialize([Options::REGISTRATION]),
            'termOfService' => $terms,
            'options' => [
                'validation' => $this->config->getParameter('registration.validation'),
                'locale' => $request->getLocale(),
                'allowWorkspace' => $this->config->getParameter('registration.allow_workspace'),
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
    private function checkAccess()
    {
        if (!$this->config->getParameter('registration.self') || $this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }
    }
}
