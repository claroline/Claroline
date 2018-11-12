<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Serializes platform parameters.
 *
 * @DI\Service("claroline.serializer.parameters")
 */
class ParametersSerializer
{
    /** @var SerializerProvider */
    private $serializer;

    /** @var FinderProvider */
    private $finder;

    private $filePath;

    /**
     * ParametersSerializer constructor.
     *
     * @DI\InjectParams({
     *     "serializer"    = @DI\Inject("claroline.api.serializer"),
     *     "finder"        = @DI\Inject("claroline.api.finder"),
     *     "filePath"      = @DI\Inject("%claroline.param.platform_options%"),
     *     "configHandler" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     *
     * @param PlatformConfigurationHandler $config
     * @param SerializerProvider           $serializer
     * @param FinderProvider               $finder
     */
    public function __construct(
        SerializerProvider $serializer,
        FinderProvider $finder,
        PlatformConfigurationHandler $configHandler,
        $filePath
    ) {
        $this->serializer = $serializer;
        $this->finder = $finder;
        $this->arrayUtils = new ArrayUtils();
        $this->filePath = $filePath;
        $this->configHandler = $configHandler;
    }

    public function serialize(array $options = [])
    {
        $data = $this->configHandler->getParameters();

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $data['tos']['text'] = $this->serializeTos();
        }

        return $data;
    }

    /**
     * Deserializes the parameters list.
     *
     * @param array $data - the data to deserialize
     *
     * @return PlatformConfiguration
     */
    public function deserialize(array $data)
    {
        $original = $data;
        $this->deserializeTos($data);
        unset($data['tos']['text']);

        $data = array_merge($this->serialize([Options::SERIALIZE_MINIMAL]), $data);
        ksort($data);
        $data = json_encode($data, JSON_PRETTY_PRINT);

        file_put_contents($this->filePath, $data);

        return $original;
    }

    public function serializeTos()
    {
        $result = $this->finder->search(
            'Claroline\CoreBundle\Entity\Content',
            ['filters' => ['type' => 'termsOfService']],
            ['property' => 'content']
        )['data'];

        if (count($result) > 0) {
            return $result[0];
        } else {
            $content = new Content();
            $content->setType('termsOfService');

            return $this->serializer->serialize($content);
        }
    }

    public function deserializeTos(array $data)
    {
        if (isset($data['tos'])) {
            $contentTos = $this->finder->fetch('Claroline\CoreBundle\Entity\Content', ['type' => 'termsOfService'], [], 0, 10);

            if (0 === count($contentTos)) {
                $contentTos = new Content();
                $contentTos->setType('termsOfService');
            } else {
                $contentTos = $contentTos[0];
            }

            $serializer = $this->serializer->get('Claroline\CoreBundle\Entity\Content');
            $serializer->deserialize($data['tos']['text'], $contentTos, ['property' => 'content']);
        }
    }
}
