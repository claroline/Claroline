<?php

namespace Claroline\PrivacyBundle\Component\Tool;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\ToolComponent;
use Claroline\CoreBundle\Component\Context\AdministrationContext;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\PrivacyBundle\Manager\PrivacyManager;

class PrivacyTool extends ToolComponent
{
    public function __construct(
        private readonly PrivacyManager $privacyManager,
        private readonly SerializerProvider $serializer
    ) {
    }

    public static function getName(): string
    {
        return 'privacy';
    }

    public static function getIcon(): string
    {
        return 'scale-balanced';
    }

    public function supportsContext(string $context): bool
    {
        return AdministrationContext::getName() === $context;
    }

    public function isRequired(string $context, ?ContextSubjectInterface $contextSubject = null): bool
    {
        return true;
    }

    public function open(OrderedTool $tool, string $context, ?ContextSubjectInterface $contextSubject = null): ?array
    {
        $privacyParameters = $this->privacyManager->getParameters();
        $serializedParameters = $this->serializer->serialize($privacyParameters);

        return [
            'privacy' => $serializedParameters,
        ];
    }
}
