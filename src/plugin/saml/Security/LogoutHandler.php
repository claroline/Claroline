<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 12/6/16
 */

namespace Claroline\SamlBundle\Security;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use LightSaml\Binding\AbstractBinding;
use LightSaml\Binding\BindingFactory;
use LightSaml\Binding\BindingFactoryInterface;
use LightSaml\Build\Container\BuildContainerInterface;
use LightSaml\Context\Profile\MessageContext;
use LightSaml\Helper;
use LightSaml\Model\Assertion\Issuer;
use LightSaml\Model\Assertion\NameID;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Metadata\SingleLogoutService;
use LightSaml\Model\Protocol\LogoutRequest;
use LightSaml\Model\Protocol\LogoutResponse;
use LightSaml\Model\Protocol\SamlMessage;
use LightSaml\Model\Protocol\Status;
use LightSaml\Model\Protocol\StatusCode;
use LightSaml\Provider\EntityDescriptor\EntityDescriptorProviderInterface;
use LightSaml\SamlConstants;
use LightSaml\State\Sso\SsoSessionState;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutHandler implements LogoutSuccessHandlerInterface
{
    private $config;
    private $entityDescriptor;
    private $bindingFactory;
    private $buildContainer;
    private $router;

    public function __construct(
        PlatformConfigurationHandler $config,
        EntityDescriptorProviderInterface $entityDescriptor,
        BindingFactoryInterface $bindingFactory,
        BuildContainerInterface $buildContainer,
        RouterInterface $router
    ) {
        $this->config = $config;
        $this->entityDescriptor = $entityDescriptor;
        $this->bindingFactory = $bindingFactory;
        $this->buildContainer = $buildContainer;
        $this->router = $router;
    }

    /**
     * Send logout to SAML idp.
     *
     * @return Response
     */
    public function onLogoutSuccess(Request $request)
    {
        if ($this->config->getParameter('saml.active')) {
            $bindingFactory = new BindingFactory();
            $bindingType = $bindingFactory->detectBindingType($request);
            if (empty($bindingType)) {
                // no SAML request: initiate logout
                if ($this->config->getParameter('saml.logout')) {
                    $response = $this->sendLogoutRequest();
                    if ($response) {
                        return $response;
                    }
                }
            } else {
                $messageContext = new MessageContext();
                $binding = $bindingFactory->create($bindingType);
                /* @var $binding AbstractBinding */

                $binding->receive($request, $messageContext);

                $samlRequest = $messageContext->getMessage();

                if ($samlRequest instanceof LogoutResponse) {
                    // back from IdP after all other SP have been disconnected
                    $status = $samlRequest->getStatus();
                    $code = $status->getStatusCode() ? $status->getStatusCode()->getValue() : null;
                    if (SamlConstants::STATUS_PARTIAL_LOGOUT === $code || SamlConstants::STATUS_SUCCESS === $code) {
                        // OK, logout
                        $session = $request->getSession();
                        $session->invalidate();

                        return new RedirectResponse(
                            $this->router->generate('claro_index')
                        );
                    }

                    // TODO: handle errors from IdP
                } elseif ($samlRequest instanceof LogoutRequest) {
                    // logout request from IdP, initiated by another SP
                    $response = $this->sendLogoutResponse($samlRequest);

                    // clean session
                    $session = $request->getSession();
                    $session->invalidate();

                    return $response;
                }
            }
        }

        return new RedirectResponse(
            $this->router->generate('claro_index')
        );
    }

    /**
     * Send a logout request to the IdP.
     *
     * @return Response
     */
    private function sendLogoutRequest()
    {
        //  <LogoutRequest
        //    xmlns="urn:oasis:names:tc:SAML:2.0:protocol"
        //    ID="_6210989d671b429f1c82467626ffd0be990ded60bd"
        //    Version="2.0"
        //    IssueInstant="2013-11-07T16:07:25Z"
        //    Destination="https://b1.bead.loc/adfs/ls/"
        //    NotOnOrAfter="2013-11-07T16:07:25Z"
        //  >
        //    <saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
        //      https://mt.evo.team/simplesaml/module.php/saml/sp/metadata.php/default-sp
        //    </saml:Issuer>
        //    <saml:NameID
        //      xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
        //      Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient"
        //    >
        //      user
        //    </saml:NameID>
        //    <SessionIndex>_677952a2-7fb3-4e7a-b439-326366e677db</SessionIndex>
        //  </LogoutRequest>

        $sessions = $this->buildContainer->getStoreContainer()->getSsoStateStore()->get()->getSsoSessions();
        if (!empty($sessions)) {
            $session = $sessions[count($sessions) - 1];
            /* @var $session SsoSessionState */

            $idp = $this->buildContainer->getPartyContainer()->getIdpEntityDescriptorStore()->get($session->getIdpEntityId());
            /* @var $idp EntityDescriptor */

            $slo = $idp->getFirstIdpSsoDescriptor()->getFirstSingleLogoutService();
            /* @var $slo SingleLogoutService */

            $logoutRequest = new LogoutRequest();
            $logoutRequest
                ->setSessionIndex($session->getSessionIndex())
                ->setNameID(new NameID(
                    $session->getNameId(), $session->getNameIdFormat()
                ))
                ->setDestination($slo->getLocation())
                ->setID(Helper::generateID())
                ->setIssueInstant(new \DateTime())
                ->setIssuer(new Issuer($this->config->getParameter('saml.entity_id')))
                ->setSignature($this->entityDescriptor->getOwnSignature())
            ;

            $context = new MessageContext();
            $context->setBindingType($slo->getBinding());
            $context->setMessage($logoutRequest);

            $binding = $this->bindingFactory->create($slo->getBinding());

            return $binding->send($context);
        }

        return null;
    }

    /**
     * Send a Success response to a logout request from the IdP.
     *
     * @return Response
     */
    private function sendLogoutResponse(SamlMessage $samlRequest)
    {
        //  <LogoutResponse
        //    xmlns="urn:oasis:names:tc:SAML:2.0:protocol"
        //    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
        //    ID="_6c3737282f007720e736f0f4028feed8cb9b40291c"
        //    Version="2.0"
        //    IssueInstant="2014-07-18T01:13:06Z"
        //    Destination="http://sp.example.com/demo1/index.php?acs"
        //    InResponseTo="ONELOGIN_21df91a89767879fc0f7df6a1490c6000c81644d"
        //  >
        //    <saml:Issuer>http://idp.example.com/metadata.php</saml:Issuer>
        //    <samlp:Status>
        //      <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success"/>
        //    </samlp:Status>
        //  </samlp:LogoutResponse>

        $idp = $this->buildContainer->getPartyContainer()->getIdpEntityDescriptorStore()->get(0);
        /* @var $idp EntityDescriptor */

        $slo = $idp->getFirstIdpSsoDescriptor()->getFirstSingleLogoutService();
        /* @var $slo SingleLogoutService */

        $message = new LogoutResponse();
        $message
            ->setRelayState($samlRequest->getRelayState())
            ->setStatus(new Status(
                new StatusCode(SamlConstants::STATUS_SUCCESS)
            ))
            ->setDestination($slo->getLocation())
            ->setInResponseTo($samlRequest->getID())
            ->setID(Helper::generateID())
            ->setIssueInstant(new \DateTime())
            /* here, the SP entity id is a container parameter, change it as you wish */
            ->setIssuer(new Issuer($this->config->getParameter('saml.entity_id')))
        ;

        $context = new MessageContext();
        $context->setBindingType($slo->getBinding());
        $context->setMessage($message);

        $binding = $this->bindingFactory->create($slo->getBinding());

        return $binding->send($context);
    }
}
