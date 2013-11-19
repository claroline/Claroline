<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Transformer\Badge;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use IDCI\Bundle\ExporterBundle\Transformer\TwigTransformer;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.transformer.badge")
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
     * transform
     *
     * @param  \CLaroline\CoreBundle\Entity\Badge\Badge $entity
     * @param  string                                   $format
     * @return string
     */
    public function transform($entity, $format)
    {
        $templatePath = __DIR__ . '/../../Resources/exporter/Badge';
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
