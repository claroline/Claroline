<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Content;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

/**
 * Serializes platform parameters.
 */
class ParametersSerializer
{
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var PlatformConfigurationHandler */
    private $configHandler;

    public function __construct(
        SerializerProvider $serializer, // bad
        ObjectManager $om,
        PlatformConfigurationHandler $configHandler
    ) {
        $this->serializer = $serializer;
        $this->configHandler = $configHandler;
        $this->om = $om;
    }

    public function getName()
    {
        return 'parameters';
    }

    public function serialize(array $options = [])
    {
        $data = $this->configHandler->getParameters();

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $data['tos']['text'] = $this->serializeTos();
        }

        $data['javascripts'] = $this->serializeAssets('javascripts', $data);
        $data['stylesheets'] = $this->serializeAssets('stylesheets', $data);

        return $data;
    }

    /**
     * Deserializes the parameters list.
     *
     * @param array $data - the data to deserialize
     *
     * @return array
     */
    public function deserialize(array $data)
    {
        $original = $data;
        $this->deserializeTos($data);
        $data = $this->getAssetsData('javascripts', $data);
        $data = $this->getAssetsData('stylesheets', $data);
        $data = $this->getLogoData($data);

        if (!empty($data['mailer'])) {
            $data['mailer'] = $this->deserializeMailer($data['mailer']);
        }

        // TODO : move this somewhere else
        unset($data['tos']['text']);

        $this->configHandler->setParameters($data);

        return $original;
    }

    private function deserializeMailer($data)
    {
        if (isset($data['transport']) && 'gmail' === $data['transport']) {
            $data['host'] = 'smtp.gmail.com';
            $data['auth_mode'] = 'login';
            $data['encryption'] = 'ssl';
            $data['port'] = '465';
        }

        return $data;
    }

    private function serializeTos()
    {
        $result = $this->om->getRepository(Content::class)->findOneBy(['type' => 'termsOfService']);
        if ($result) {
            return $this->serializer->serialize($result, ['property' => 'content']);
        }

        $content = new Content();
        $content->setType('termsOfService');

        return $this->serializer->serialize($content);
    }

    private function getAssetsData($name, array $data)
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

    private function deserializeTos(array $data)
    {
        if (isset($data['tos'])) {
            $contentTos = $this->om->getRepository(Content::class)->findOneBy([
                'type' => 'termsOfService',
            ]);

            if (empty($contentTos)) {
                $contentTos = new Content();
                $contentTos->setType('termsOfService');
            }

            $serializer = $this->serializer->get(Content::class);

            if (isset($data['tos']['text'])) {
                $serializer->deserialize($data['tos']['text'], $contentTos, ['property' => 'content']);
            }
        }
    }

    private function serializeAssets($name, array $data)
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

    private function getLogoData(array $data)
    {
        if (isset($data['display']) && isset($data['display']['logo'])) {
            $logo = $data['display']['logo'];
            if (isset($logo['url'])) {
                $data['display']['logo'] = $logo['url'];
            }
        }

        return $data;
    }
}
