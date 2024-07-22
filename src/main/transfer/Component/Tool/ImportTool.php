<?php

namespace Claroline\TransferBundle\Component\Tool;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\TransferBundle\Transfer\ImportProvider;

class ImportTool extends AbstractTool
{
    public function __construct(
        private readonly ImportProvider $importProvider,
        private readonly SerializerProvider $serializer
    ) {
    }

    public static function getName(): string
    {
        return 'import';
    }

    public static function getIcon(): string
    {
        return 'file-import';
    }

    public function supportsContext(string $context): bool
    {
        return in_array($context, [
            DesktopContext::getName(),
            WorkspaceContext::getName(),
        ]);
    }

    public function open(string $context, ContextSubjectInterface $contextSubject = null): ?array
    {
        $options = [];
        $extra = [];
        if ($context === WorkspaceContext::getName()) {
            $options[] = Options::WORKSPACE_IMPORT;
            $extra['workspace'] = $this->serializer->serialize($contextSubject, [Options::SERIALIZE_MINIMAL]);
        }

        return [
            'explanation' => $this->importProvider->getAvailableActions('csv', $options, $extra),
            'samples' => $this->importProvider->getSamples('csv', $options, $extra),
        ];
    }
}
