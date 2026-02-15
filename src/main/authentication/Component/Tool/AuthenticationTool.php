<?php

namespace Claroline\AuthenticationBundle\Component\Tool;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\ToolComponent;
use Claroline\AuthenticationBundle\Entity\OAuthClient;
use Claroline\AuthenticationBundle\Manager\AuthenticationManager;
use Claroline\AuthenticationBundle\Manager\OAuthManager;
use Claroline\CoreBundle\Component\Context\AdministrationContext;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthenticationTool extends ToolComponent
{
    public function __construct(
        private readonly UrlGeneratorInterface $router,
        private readonly SerializerProvider $serializer,
        private readonly AuthenticationManager $authenticationManager,
        private readonly OAuthManager $oauthManager,
    ) {
    }

    public static function getName(): string
    {
        return 'authentication';
    }

    public static function getIcon(): string
    {
        return 'shield-alt';
    }

    public function isRequired(string $context, ?ContextSubjectInterface $contextSubject = null): bool
    {
        return true;
    }

    public function supportsContext(string $context): bool
    {
        return AdministrationContext::getName() === $context;
    }

    public function open(OrderedTool $tool, string $context, ?ContextSubjectInterface $contextSubject = null): ?array
    {
        return [
            'authentication' => $this->serializer->serialize(
                $this->authenticationManager->getParameters()
            ),
            'oauthRedirect' => $this->router->generate('claro_security_login_check_oauth2', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'oauthProviders' => $this->oauthManager->getAvailableProviders(),
            'oauthClients' => array_map(function (OAuthClient $client) {
                return $this->serializer->serialize($client);
            }, $this->oauthManager->getAvailableClients()),
        ];
    }
}
