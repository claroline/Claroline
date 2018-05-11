<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Twig;

use Claroline\CoreBundle\API\Serializer\PlatformSerializer;
use JMS\DiExtraBundle\Annotation as DI;
use Twig_Extension;

/**
 * Exposes Platform configuration to Twig templates.
 *
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class PlatformConfigurationExtension extends Twig_Extension
{
    /** @var PlatformSerializer */
    private $serializer;

    /**
     * PlatformConfigurationExtension constructor.
     *
     * @DI\InjectParams({
     *     "serializer" = @DI\Inject("claroline.serializer.platform")
     * })
     *
     * @param PlatformSerializer $serializer
     */
    public function __construct(
        PlatformSerializer $serializer
    ) {
        $this->serializer = $serializer;
    }

    public function getName()
    {
        return 'claro_platform_configuration';
    }

    public function getFunctions()
    {
        return [
            'platform_config' => new \Twig_SimpleFunction('platform_config', [$this, 'getPlatformConfig']),
        ];
    }

    public function getPlatformConfig()
    {
        return $this->serializer->serialize();
    }
}
