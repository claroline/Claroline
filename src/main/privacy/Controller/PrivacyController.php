<?php

namespace Claroline\PrivacyBundle\Controller;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\PrivacyBundle\Entity\Privacy;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PrivacyController extends AbstractCrudController
{
    use RequestDecoderTrait;

    private AuthorizationCheckerInterface $authorization;
    private PlatformConfigurationHandler $config;
    private SerializerProvider $privacySerializer;
    private ObjectManager $objectManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        PlatformConfigurationHandler $ch,
        SerializerProvider $privacySerializer,
        ObjectManager $objectManager
    ) {
        $this->authorization = $authorization;
        $this->config = $ch;
        $this->privacySerializer = $privacySerializer;
        $this->objectManager = $objectManager;
    }

    /**
     * @Route("/privacy")
     */
    public function getName(): string
    {
        return 'privacy';
    }

    public function getClass(): string
    {
       return Privacy::class;
    }

    /**
     * @Route("privacy/save-country-storage", name="apiv2_privacy_update_country_storage", methods={"PUT"})
     */
    public function updatePrivacyStorage(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $privacy = $this->objectManager->getRepository(Privacy::class)->findOneBy([], ['id' => 'ASC']);
        $privacy->setCountryStorage($data['countryStorage']);
        $this->objectManager->flush();

        return new JsonResponse([
            'message' => 'Privacy storage updated successfully',
            'countryStorage' => $privacy->getCountryStorage()
        ]);
    }

    /**
     * @Route("privacy/save-dpo", name="apiv2_privacy_update_dpo", methods={"PUT"})
     */
    public function updatePrivacyDpo(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $privacy = $this->objectManager->getRepository(Privacy::class)->findOneBy([], ['id' => 'ASC']);

        $privacy->setDpoName($data['dpo']['name']);
        $privacy->setDpoEmail($data['dpo']['email']);
        $privacy->setDpoPhone($data['dpo']['phone']);
        $privacy->setAddressStreet1($data['dpo']['address']['street1']);
        $privacy->setAddressStreet2($data['dpo']['address']['street2']);
        $privacy->setAddressPostalCode($data['dpo']['address']['postalCode']);
        $privacy->setAddressCity($data['dpo']['address']['city']);
        $privacy->setAddressState($data['dpo']['address']['state']);
        $privacy->setAddressCountry($data['dpo']['address']['country']);

        $this->objectManager->flush();

        return new JsonResponse([
            'message' => 'DPO information updated successfully',
            'dpo' => [
                'name' => $privacy->getDpoName(),
                'email' => $privacy->getDpoEmail(),
                'phone' => $privacy->getDpoPhone(),
                'address' => [
                    'street1' => $privacy->getAddressStreet1(),
                    'street2' => $privacy->getAddressStreet2(),
                    'postalCode' => $privacy->getAddressPostalCode(),
                    'city' => $privacy->getAddressCity(),
                    'state' => $privacy->getAddressState(),
                    'country' => $privacy->getAddressCountry(),
                ],
            ],
        ]);
    }

    /**
     * @Route("privacy/save-terms", name="apiv2_privacy_update_terms", methods={"PUT"})
     */
    public function updatePrivacyTerms(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $privacy = $this->objectManager->getRepository(Privacy::class)->findOneBy([], ['id' => 'ASC']);

        $privacy->setIsTermsOfServiceEnabled($data['isTermsOfService']);
        $privacy->setTermsOfService($data['termsOfService']);

        $this->objectManager->flush();

        return new JsonResponse([
            'message' => 'Terms of Service updated successfully',
            'isTermsOfService' => $privacy->getIsTermsOfServiceEnabled(),
            'termsOfService' => $privacy->getTermsOfService(),
        ]);
    }
}
