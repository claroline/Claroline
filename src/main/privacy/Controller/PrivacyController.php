<?php

namespace Claroline\PrivacyBundle\Controller;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\PrivacyBundle\Manager\MailManager;
use Claroline\PrivacyBundle\Manager\PrivacyManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class PrivacyController extends AbstractSecurityController
{
    use RequestDecoderTrait;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly PlatformConfigurationHandler $config,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly PrivacyManager $manager,
        private readonly MailManager $mailManager
    ) {
    }

    #[Route(path: '/terms_of_service', name: 'apiv2_platform_terms_of_service', methods: ['GET'])]
    public function getTermsAction(Request $request): JsonResponse
    {
        $tos = null;
        if ($this->manager->getTosEnabled($request->getLocale())) {
            $tos = $this->manager->getTosContent($request->getLocale());
        }

        return new JsonResponse($tos);
    }

    #[Route(path: '/terms_of_service/accept', name: 'apiv2_platform_terms_of_service_accept', methods: ['PUT'])]
    public function acceptTermsAction(#[CurrentUser] ?User $currentUser): JsonResponse
    {
        if (null === $currentUser) {
            return new JsonResponse(null, 204);
        }

        $currentUser->setAcceptedTerms(true);

        $this->om->persist($currentUser);
        $this->om->flush();

        return new JsonResponse($this->serializer->serialize($currentUser));
    }

    #[Route(path: '/privacy', name: 'apiv2_privacy_get', methods: ['GET'])]
    public function getAction(Request $request): JsonResponse
    {
        $privacyParameters = $this->manager->getParameters();

        return new JsonResponse([
            'countryStorage' => $privacyParameters->getCountryStorage(),
            'dpo' => [
                'name' => $privacyParameters->getDpoName(),
                'email' => $privacyParameters->getDpoEmail(),
                'address' => [
                    'street1' => $privacyParameters->getDpoAddressStreet1(),
                    'street2' => $privacyParameters->getDpoAddressStreet2(),
                    'postalCode' => $privacyParameters->getDpoAddressPostalCode(),
                    'city' => $privacyParameters->getDpoAddressCity(),
                    'state' => $privacyParameters->getDpoAddressState(),
                    'country' => $privacyParameters->getDpoAddressCountry(),
                ],
                'phone' => $privacyParameters->getDpoPhone(),
            ],
            'content' => $this->manager->getPrivacyContent($request->getLocale()),
        ]);
    }

    /**
     * Updates privacy parameters of the platform.
     */
    #[Route(path: '/privacy', name: 'apiv2_privacy_update', methods: ['PUT'])]
    public function updateAction(Request $request): JsonResponse
    {
        $this->canOpenAdminTool('privacy');

        $data = $this->decodeRequest($request);

        $privacyParameters = $this->manager->getParameters();

        $updatedPrivacyParameters = $this->serializer->deserialize($data, $privacyParameters);

        $this->manager->updateParameters($updatedPrivacyParameters);

        return new JsonResponse(
            $this->serializer->serialize($updatedPrivacyParameters)
        );
    }

    #[Route(path: '/request-deletion', name: 'apiv2_request_account_deletion', methods: ['POST'])]
    public function requestAccountDeletionAction(#[CurrentUser] ?User $currentUser): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $this->mailManager->sendRequestToDPO($currentUser);

        return new JsonResponse(null, 204);
    }
}
