<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

/**
 * Serializes platform parameters.
 */
class ParametersSerializer
{
    public function __construct(
        private readonly PlatformConfigurationHandler $configHandler
    ) {
    }

    public function getName(): string
    {
        return 'parameters';
    }

    public function serialize(): array
    {
        return $this->configHandler->getParameters();
    }

    /**
     * Deserializes the parameters list.
     *
     * @param array $data - the data to deserialize
     */
    public function deserialize(array $data): array
    {
        $original = $data;

        if (!empty($data['mailer'])) {
            $data['mailer'] = $this->deserializeMailer($data['mailer']);
        }

        $this->configHandler->setParameters($data);

        return $original;
    }

    private function deserializeMailer(array $data): array
    {
        if (isset($data['transport']) && 'gmail' === $data['transport']) {
            $data['host'] = 'smtp.gmail.com';
            $data['auth_mode'] = 'login';
            $data['encryption'] = 'ssl';
            $data['port'] = '465';
        }

        return $data;
    }
}
