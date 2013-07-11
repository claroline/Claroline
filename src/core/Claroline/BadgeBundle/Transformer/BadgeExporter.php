<?php

namespace Claroline\BadgeBundle\Transformer;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use IDCI\Bundle\ExporterBundle\Transformer\TwigTransformer;

class BadgeExporter extends TwigTransformer
{
    /**
     * @var PlatformConfigurationHandler
     */
    private $platformConfigHandler;

    /**
     * Constructor
     */
    public function __construct($container, PlatformConfigurationHandler $platformConfigHandler)
    {
        parent::__construct($container);

        $this->platformConfigHandler = $platformConfigHandler;
    }

    /**
     * transform
     *
     * @param \CLaroline\BadgeBundle\Entity\Badge $entity
     * @param string $format
     * @return string
     */
    public function transform($entity, $format)
    {
        $templatePath = $this->getTemplatePath($entity, $format);
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
