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
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Serializes platform parameters.
 *
 * @DI\Service("claroline.serializer.parameters")
 */
class ParametersSerializer
{
    /** @var ObjectManager */
    private $om;

    /** @var SerializerProvider */
    private $serializer;

    /** @var FinderProvider */
    private $finder;

    /** @var PlatformConfigurationHandler */
    private $configHandler;

    /** @var string */
    private $filePath;

    /**
     * ParametersSerializer constructor.
     *
     * @DI\InjectParams({
     *     "serializer"    = @DI\Inject("claroline.api.serializer"),
     *     "finder"        = @DI\Inject("claroline.api.finder"),
     *     "filePath"      = @DI\Inject("%claroline.param.platform_options%"),
     *     "configHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "ism"           = @DI\Inject("claroline.manager.icon_set_manager"),
     *     "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *     "archivePath"   = @DI\Inject("%claroline.param.archive_directory%")
     * })
     *
     * @param SerializerProvider           $serializer
     * @param FinderProvider               $finder
     * @param ObjectManager                $om
     * @param PlatformConfigurationHandler $configHandler
     * @param string                       $filePath
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

    public function serialize(array $options = [])
    {
        $data = $this->configHandler->getParameters();

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $data['tos']['text'] = $this->serializeTos();
        }

        $data['javascripts'] = $this->serializeJavascripts($data);
        $data['display']['logo'] = $this->serializeAppearanceLogo($data);
        //maybe move this somewhere else
        $data['archives'] = $this->serializeArchive($data);

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
        $data = $this->getJavascriptsData($data);
        $data = $this->getLogoData($data);
        unset($data['tos']['text']);
        //maybe move this somewhere else
        unset($data['archives']);

        $data = array_merge($this->serialize([Options::SERIALIZE_MINIMAL]), $data);
        ksort($data);
        $data = json_encode($data, JSON_PRETTY_PRINT);

        file_put_contents($this->filePath, $data);

        return $original;
    }

    public function serializeTos()
    {
        $result = $this->om->getRepository(Content::class)->findBy(['type' => 'termsOfService']);

        if (count($result) > 0) {
            return $result[0]->getContent();
        } else {
            $content = new Content();
            $content->setType('termsOfService');

            return $this->serializer->serialize($content);
        }
    }

    public function getJavascriptsData(array $data)
    {
        $javascripts = $data['javascripts'];
        $data['javascripts'] = [];
        //maybe validate its a real javascript file here

        foreach ($javascripts as $javascript) {
            $data['javascripts'][] = $javascript['url'];
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

    public function serializeJavascripts(array $data)
    {
        $uploadedFiles = [];

        foreach ($data['javascripts'] as $url) {
            $file = $this->om->getRepository(PublicFile::class)->findOneBy(['url' => $url]);
            $uploadedFiles[] = $this->serializer->serialize($file);
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
