<?php

namespace Claroline\AuthenticationBundle\Component\Tool;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\AuthenticationBundle\Manager\AuthenticationManager;
use Claroline\CoreBundle\Component\Context\AccountContext;
use Claroline\CoreBundle\Component\Context\AdministrationContext;

class AuthenticationTool extends AbstractTool
{
    public function __construct(
        private readonly SerializerProvider $serializer,
        private readonly AuthenticationManager $authenticationManager
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

    public function isRequired(string $context, ContextSubjectInterface $contextSubject = null): bool
    {
        return true;
    }

    public function supportsContext(string $context): bool
    {
        return in_array($context, [
            AccountContext::getName(),
            AdministrationContext::getName(),
        ]);
    }

    public function open(string $context, ContextSubjectInterface $contextSubject = null): ?array
    {
        if (AdministrationContext::getName() === $context) {
            return [
                'authentication' => $this->serializer->serialize(
                    $this->authenticationManager->getParameters()
                ),
            ];
        }

        return [];
    }
}
