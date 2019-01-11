<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UJM\LtiBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use UJM\LtiBundle\Entity\LtiApp;
use UJM\LtiBundle\Entity\LtiResource;

/**
 * @DI\Service("claroline.serializer.lti.resource")
 * @DI\Tag("claroline.serializer")
 */
class LtiResourceSerializer
{
    use SerializerTrait;

    /** @var RequestStack */
    private $requestStack;
    /** @var SerializerProvider */
    private $serializer;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var TranslatorInterface */
    private $translator;
    /** @var WorkspaceManager */
    private $workspaceManager;

    private $ltiAppRepo;

    /**
     * LtiResourceSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"               = @DI\Inject("claroline.persistence.object_manager"),
     *     "requestStack"     = @DI\Inject("request_stack"),
     *     "serializer"       = @DI\Inject("claroline.api.serializer"),
     *     "tokenStorage"     = @DI\Inject("security.token_storage"),
     *     "translator"       = @DI\Inject("translator"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     *
     * @param ObjectManager         $om
     * @param RequestStack          $requestStack
     * @param SerializerProvider    $serializer
     * @param TokenStorageInterface $tokenStorage
     * @param TranslatorInterface   $translator
     * @param WorkspaceManager      $workspaceManager
     */
    public function __construct(
        ObjectManager $om,
        RequestStack $requestStack,
        SerializerProvider $serializer,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        WorkspaceManager $workspaceManager
    ) {
        $this->requestStack = $requestStack;
        $this->serializer = $serializer;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->workspaceManager = $workspaceManager;

        $this->ltiAppRepo = $om->getRepository(LtiApp::class);
    }

    /**
     * Serializes a LTI resource for the JSON api.
     *
     * @param LtiResource $ltiResource - the LTI resource to serialize
     * @param array       $options     - a list of serialization options
     *
     * @return array - the serialized representation of the LTI resource
     */
    public function serialize(LtiResource $ltiResource, array $options = [])
    {
        $serialized = [
            'id' => $ltiResource->getUuid(),
            'openInNewTab' => $ltiResource->getOpenInNewTab(),
            'ratio' => $ltiResource->getRatio(),
            'ltiApp' => $ltiResource->getLtiApp() ?
                $this->serializer->serialize($ltiResource->getLtiApp(), [Options::SERIALIZE_MINIMAL]) :
                null,
            'ltiData' => $this->serializeLtiData($ltiResource),
        ];

        return $serialized;
    }

    /**
     * @param array       $data
     * @param LtiResource $ltiResource
     *
     * @return LtiResource
     */
    public function deserialize($data, LtiResource $ltiResource)
    {
        $this->sipe('openInNewTab', 'setOpenInNewTab', $data, $ltiResource);
        $this->sipe('ratio', 'setRatio', $data, $ltiResource);

        $ltiApp = isset($data['ltiApp']['id']) ?
            $this->ltiAppRepo->findOneBy(['uuid' => $data['ltiApp']['id']]) :
            null;
        $ltiResource->setLtiApp($ltiApp);

        return $ltiResource;
    }

    /**
     * @param LtiResource $ltiResource
     *
     * @return array
     */
    private function serializeLtiData(LtiResource $ltiResource)
    {
        $data = new \stdClass();
        $app = $ltiResource->getLtiApp();

        if ($app) {
            $workspace = $ltiResource->getResourceNode()->getWorkspace();
            $user = $this->tokenStorage->getToken()->getUser();
            $isAnon = 'anon.' === $user;
            $anonymous = $this->translator->trans('anonymous', [], 'platform');
            $isWorkspaceManager = $this->workspaceManager->isManager($workspace, $this->tokenStorage->getToken());
            $now = new \DateTime();

            $data = [
                'user_id' => !$isAnon ? $user->getUsername() : $anonymous,
                'roles' => $isWorkspaceManager ? 'Instructor' : 'Learner',
                'resource_link_id' => $workspace->getId(),
                'resource_link_title' => $app->getTitle(),
                'resource_link_description' => $app->getDescription(),
                'lis_person_name_full' => !$isAnon ? $user->getFirstname().' '.$user->getLastname() : $anonymous,
                'lis_person_name_family' => !$isAnon ? $user->getLastname() : $anonymous,
                'lis_person_name_given' => !$isAnon ? $user->getFirstname() : $anonymous,
                'lis_person_contact_email_primary' => !$isAnon ? $user->getEmail() : $anonymous,
                'lis_person_sourcedid' => !$isAnon ? $user->getUsername() : $anonymous,
                'context_id' => $workspace->getId(),
                'context_title' => $workspace->getName(),
                'context_label' => $workspace->getCode(),
                'tool_consumer_instance_guid' => $this->requestStack->getMasterRequest()->getSchemeAndHttpHost(),
                'tool_consumer_instance_description' => $this->requestStack->getMasterRequest()->getSchemeAndHttpHost(),
                'launch_presentation_locale' => $this->requestStack->getMasterRequest()->getLocale(),
            ];
            $data['lti_version'] = 'LTI-1p0';
            $data['lti_message_type'] = 'basic-lti-launch-request';

            //Basic LTI uses OAuth to sign requests
            //OAuth Core 1.0 spec: http://oauth.net/core/1.0/

            $data['oauth_callback'] = 'about:blank';
            $data['oauth_consumer_key'] = $app->getAppkey();
            $data['oauth_version'] = '1.0';
            $data['oauth_nonce'] = uniqid('', true);
            $data['oauth_timestamp'] = $now->getTimestamp();
            $data['oauth_signature_method'] = 'HMAC-SHA1';

            //In OAuth, request parameters must be sorted by name
            $launch_data_keys = array_keys($data);
            sort($launch_data_keys);

            $launch_params = [];

            foreach ($launch_data_keys as $key) {
                array_push($launch_params, $key.'='.rawurlencode($data[$key]));
            }

            $base_string = 'POST&'.rawurlencode($app->getUrl()).'&'.rawurlencode(implode('&', $launch_params));
            $secret = rawurlencode($app->getSecret()).'&';
            $signature = base64_encode(hash_hmac('sha1', $base_string, $secret, true));

            $data['oauth_signature'] = $signature;
        }

        return $data;
    }
}
