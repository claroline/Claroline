<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

/**
 * Serializes platform parameters.
 */
class ParametersSerializer
{
    public function __construct(
        private readonly SerializerProvider $serializer, // bad
        private readonly ObjectManager $om,
        private readonly PlatformConfigurationHandler $configHandler
    ) {
    }

    public function getName(): string
    {
        return 'parameters';
    }

    public function serialize(): array
    {
        $data = $this->configHandler->getParameters();

        $data['javascripts'] = $this->serializeAssets('javascripts', $data);
        $data['stylesheets'] = $this->serializeAssets('stylesheets', $data);

        return $data;
    }

    /**
     * Deserializes the parameters list.
     *
     * @param array $data - the data to deserialize
     */
    public function deserialize(array $data): array
    {
        $original = $data;
        $data = $this->getAssetsData('javascripts', $data);
        $data = $this->getAssetsData('stylesheets', $data);
        /*$data = $this->getLogoData($data);*/

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

    private function getAssetsData(string $name, array $data): array
    {
        if (isset($data[$name])) {
            $assets = $data[$name];
            $data[$name] = [];

            foreach ($assets as $asset) {
                if (!empty($asset)) {
                    $data[$name][] = $asset['url'];
                }
            }
        }

        return $data;
    }

    private function serializeAssets(string $name, array $data): array
    {
        $uploadedFiles = [];

        if (isset($data[$name])) {
            foreach ($data[$name] as $url) {
                $file = $this->om->getRepository(PublicFile::class)->findOneBy(['url' => $url]);
                $uploadedFiles[] = $this->serializer->serialize($file);
            }
        }

        return $uploadedFiles;
    }

    /*private function getLogoData(array $data): array
    {
        if (isset($data['display']) && isset($data['display']['logo'])) {
            $logo = $data['display']['logo'];
            if (isset($logo['url'])) {
                $data['display']['logo'] = $logo['url'];
            }
        }

        return $data;
    }*/
}
