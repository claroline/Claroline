<?php

namespace UJM\LtiBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;
use Proxies\__CG__\Claroline\CoreBundle\Entity\Workspace\Workspace;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use UJM\LtiBundle\Entity\LtiApp;
use UJM\LtiBundle\Entity\LtiResource;

/**
 * @DI\Tag("security.secure_service")
 */
class LtiWsController extends Controller
{
    /**
     * @Route("/open_app/{resource}", name="ujm_lti_open_app")
     *
     * @Template
     *
     * @param resource $resource
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function open_appAction($resource)
    {
        $em = $this->getDoctrine()->getManager();
        $ltiResource = $em->getRepository('UJMLtiBundle:LtiResource')->find($resource->getId());
        $app = $ltiResource->getLtiApp();
        $workspace = $resource->getResourceNode()->getWorkspace();
        $ltiParams = $this->getLtiData($workspace, $app);
        $vars['workspace'] = $workspace;
        $vars['ltiApp'] = $app;
        $vars['ltiDatas'] = $ltiParams['ltiData'];
        $vars['signature'] = $ltiParams['signature'];
        $vars['target'] = $resource->getOpenInNewTab();

        return $this->render('UJMLtiBundle:Lti:open_app.html.twig', $vars);
    }

    /**
     * @param Workspace $ws
     * @param LtiApp    $app
     *
     * @return mixed
     */
    private function getLtiData(Workspace $ws, LtiApp $app)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $isWorkspaceManager = $this->isWorkspaceManager($ws, $user);
        if ($isWorkspaceManager === true) {
            $role = 'Instructor';
        } else {
            $role = 'Learner';
        }
        $now = new \DateTime();

        $ltiData = [
            'user_id' => $user->getUsername(),
            'roles' => $role,
            'resource_link_id' => $ws->getId(),
            'resource_link_title' => $app->getTitle(),
            'resource_link_description' => $app->getDescription(),
            'lis_person_name_full' => $user->getFirstname().' '.$user->getLastname(),
            'lis_person_name_family' => $user->getLastname(),
            'lis_person_name_given' => $user->getFirstname(),
            'lis_person_contact_email_primary' => $user->getEmail(),
            'lis_person_sourcedid' => $user->getUsername(),
            'context_id' => $ws->getId(),
            'context_title' => $ws->getName(),
            'context_label' => $ws->getCode(),
            'tool_consumer_instance_guid' => $this->get('request')->getSchemeAndHttpHost(),
            'tool_consumer_instance_description' => $this->get('request')->getSchemeAndHttpHost(),
            'launch_presentation_locale' => $this->get('request')->getLocale(),
        ];
        $ltiData['lti_version'] = 'LTI-1p0';
        $ltiData['lti_message_type'] = 'basic-lti-launch-request';

        //Basic LTI uses OAuth to sign requests
        //OAuth Core 1.0 spec: http://oauth.net/core/1.0/

        $ltiData['oauth_callback'] = 'about:blank';
        $ltiData['oauth_consumer_key'] = $app->getAppkey();
        $ltiData['oauth_version'] = '1.0';
        $ltiData['oauth_nonce'] = uniqid('', true);
        $ltiData['oauth_timestamp'] = $now->getTimestamp();
        $ltiData['oauth_signature_method'] = 'HMAC-SHA1';

        //In OAuth, request parameters must be sorted by name
        $launch_data_keys = array_keys($ltiData);
        sort($launch_data_keys);

        $launch_params = [];
        foreach ($launch_data_keys as $key) {
            array_push($launch_params, $key.'='.rawurlencode($ltiData[$key]));
        }

        $base_string = 'POST&'.rawurlencode($app->getUrl()).'&'.rawurlencode(implode('&', $launch_params));
        $secret = rawurlencode($app->getSecret()).'&';
        $signature = base64_encode(hash_hmac('sha1', $base_string, $secret, true));

        $ltiParams['ltiData'] = $ltiData;
        $ltiParams['signature'] = $signature;

        return $ltiParams;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param User                                             $user
     *
     * @return bool
     */
    private function isWorkspaceManager(Workspace $workspace, User $user)
    {
        $isWorkspaceManager = false;
        $managerRole = 'ROLE_WS_MANAGER_'.$workspace->getGuid();
        $roleNames = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roleNames) || in_array($managerRole, $roleNames)) {
            $isWorkspaceManager = true;
        }

        return $isWorkspaceManager;
    }
}
