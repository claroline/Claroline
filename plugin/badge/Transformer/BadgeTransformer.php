<?php

namespace Icap\BadgeBundle\Transformer;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use IDCI\Bundle\ExporterBundle\Transformer\TwigTransformer;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap_badge.transformer.badge")
 */
class BadgeTransformer extends TwigTransformer
{
    /**
     * @var PlatformConfigurationHandler
     */
    private $platformConfigHandler;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "container"               = @DI\Inject("service_container"),
     *     "platformConfigHandler"   = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct($container, PlatformConfigurationHandler $platformConfigHandler)
    {
        parent::__construct($container);

        $this->platformConfigHandler = $platformConfigHandler;
    }

    /**
     * transform.
     *
     * @param \Icap\BadgeBundle\Entity\Badge $entity
     * @param string                         $format
     *
     * @return string
     */
    public function transform($entity, $format)
    {
        $templatePath = __DIR__.'/../../Resources/exporter/Badge';
        $this->container->get('twig.loader')->addPath($templatePath);

        $templateNameFormat = $this->getTemplateNameFormat($entity, $format);
        $template = sprintf($templateNameFormat, $format);

        $entity->setLocale($this->platformConfigHandler->getParameter('locale_language'));

        return $this->getTemplate()->render(
            $template,
            array('entity' => $entity)
        );
    }
}
