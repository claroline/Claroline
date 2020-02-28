<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Content;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\IconSetManager;

/**
 * Serializes platform parameters.
 */
class ParametersSerializer
{
    /** @var ObjectManager */
    private $om;

    /** @var SerializerProvider */
    private $serializer;

    /** @var FinderProvider */
    private $finder;

    /** @var IconSetManager */
    private $ism;

    /** @var PlatformConfigurationHandler */
    private $configHandler;

    /** @var string */
    private $filePath;

    /** @var string */
    private $archivePath;

    /**
     * ParametersSerializer constructor.
     *
     * @param SerializerProvider           $serializer
     * @param IconSetManager               $ism
     * @param FinderProvider               $finder
     * @param ObjectManager                $om
     * @param PlatformConfigurationHandler $configHandler
     * @param string                       $filePath
     * @param string                       $archivePath
     */
    public function __construct(
        SerializerProvider $serializer, // bad
        FinderProvider $finder, // bad
        IconSetManager $ism,
        ObjectManager $om,
        PlatformConfigurationHandler $configHandler,
        $filePath,
        $archivePath
    ) {
        $this->serializer = $serializer;
        $this->finder = $finder;
        $this->filePath = $filePath;
        $this->configHandler = $configHandler;
        $this->om = $om;
        $this->ism = $ism;
        $this->archivePath = $archivePath;
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
        $data['display']['logo'] = $this->serializeAppearanceLogo($data);
        // TODO : move this somewhere else
        $data['archives'] = $this->serializeArchive();

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

        if (isset($data['mailer'])) {
            $data['mailer'] = $this->deserializeMailer($data['mailer']);
        }

        // TODO : move this somewhere else
        unset($data['tos']['text']);
        unset($data['archives']);

        $data = array_replace_recursive($this->serialize([Options::SERIALIZE_MINIMAL]), $data);
        ksort($data);
        $data = json_encode($data, JSON_PRETTY_PRINT);

        file_put_contents($this->filePath, $data);

        return $original;
    }

    public function deserializeMailer($data)
    {
        if ('gmail' === $data['transport']) {
            $data['host'] = 'smtp.gmail.com';
            $data['auth_mode'] = 'login';
            $data['encryption'] = 'ssl';
            $data['port'] = '465';
        }

        return $data;
    }

    public function serializeTos()
    {
        $result = $this->om->getRepository(Content::class)->findOneBy(['type' => 'termsOfService']);

        if ($result) {
            $data = $this->serializer->serialize($result, ['property' => 'content']);

            return $data;
        } else {
            $content = new Content();
            $content->setType('termsOfService');

            return $this->serializer->serialize($content);
        }
    }

    public function getAssetsData($name, array $data)
    {
        if (isset($data[$name])) {
            $assets = $data[$name];
            $data[$name] = [];

            foreach ($assets as $asset) {
                $data[$name][] = $asset['url'];
            }
        }

        return $data;
    }

    public function deserializeTos(array $data)
    {
        if (isset($data['tos'])) {
            $contentTos = $this->finder->fetch(Content::class, ['type' => 'termsOfService'], [], 0, 10);

            if (0 === count($contentTos)) {
                $contentTos = new Content();
                $contentTos->setType('termsOfService');
            } else {
                $contentTos = $contentTos[0];
            }

            $serializer = $this->serializer->get(Content::class);

            if (isset($data['tos']['text'])) {
                $serializer->deserialize($data['tos']['text'], $contentTos, ['property' => 'content']);
            }
        }
    }

    public function serializeArchive()
    {
        if (!is_dir($this->archivePath)) {
            mkdir($this->archivePath);
        }

        $iterator = new \DirectoryIterator($this->archivePath);
        $files = [];

        foreach ($iterator as $element) {
            if ($element->isFile()) {
                $files[] = $element->getFilename();
            }
        }

        return $files;
    }

    public function serializeAssets($name, array $data)
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

    public function serializeAppearanceLogo(array $data)
    {
        $url = $data['display']['logo'];
        $file = $this->om->getRepository(PublicFile::class)->findOneBy(['url' => $url]);

        return $this->serializer->serialize($file);
    }

    public function getLogoData(array $data)
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
