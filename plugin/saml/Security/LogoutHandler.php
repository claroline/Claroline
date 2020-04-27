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
use LightSaml\SamlConstants;
use LightSaml\State\Sso\SsoSessionState;
use LightSaml\SymfonyBridgeBundle\Bridge\Container\BuildContainer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutHandler implements LogoutSuccessHandlerInterface
{
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var EntityDescriptorProvider */
    private $entityDescriptorProvider;

    /** @var ContainerInterface */
    private $container;

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(PlatformConfigurationHandler::class);
        $this->entityDescriptorProvider = $this->container->get('lightsaml.own.entity_descriptor_provider');

        return $this;
    }

    /**
     * Send logout to SAML idp.
     *
     * @param Request $request
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
                $response = $this->sendLogoutRequest();
                if ($response) {
                    return $response;
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
                            $this->container->get('router')->generate('claro_index')
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
            $this->container->get('router')->generate('claro_index')
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

        $builder = $this->container->get('lightsaml.container.build');
        /* @var $builder BuildContainer */

        $sessions = $builder->getStoreContainer()->getSsoStateStore()->get()->getSsoSessions();
        if (!empty($sessions)) {
            $session = $sessions[count($sessions) - 1];
            /* @var $session SsoSessionState */

            $idp = $builder->getPartyContainer()->getIdpEntityDescriptorStore()->get(0);
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
                ->setSignature($this->entityDescriptorProvider->getOwnSignature())
            ;

            $context = new MessageContext();
            $context->setBindingType($slo->getBinding());
            $context->setMessage($logoutRequest);

            $bindingFactory = $this->container->get('lightsaml.service.binding_factory');
            /* @var $bindingFactory BindingFactory */
            $binding = $bindingFactory->create($slo->getBinding());
            /* @var $binding AbstractBinding */
            $response = $binding->send($context);

            return $response;
        }

        return null;
    }

    /**
     * Send a Success response to a logout request from the IdP.
     *
     * @param SamlMessage $samlRequest
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

        $builder = $this->container->get('lightsaml.container.build');
        /* @var $builder BuildContainer */

        $idp = $builder->getPartyContainer()->getIdpEntityDescriptorStore()->get(0);
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

        $bindingFactory = $this->container->get('lightsaml.service.binding_factory');
        /* @var $bindingFactory BindingFactory */
        $binding = $bindingFactory->create($slo->getBinding());
        /* @var $binding AbstractBinding */
        $response = $binding->send($context);

        return $response;
    }
}
