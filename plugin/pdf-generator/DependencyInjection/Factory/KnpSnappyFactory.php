<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PdfGeneratorBundle\DependencyInjection\Factory;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;
use Knp\Snappy\Pdf;

/**
 * @DI\Service("claroline.pdf_generator_factory")
 */
class KnpSnappyFactory
{
    private $configHandler;
    /**
     * @DI\InjectParams({
     *     "configHandler" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(PlatformConfigurationHandler $configHandler)
    {
        $this->configHandler = $configHandler;
    }

    public function getPdfCreator()
    {
        $pdf = new Pdf(
            $this->configHandler->getParameter('knp_pdf_binary_path'),
            ['encoding' => 'utf-8']
        );
        $pdf->setTimeout($this->configHandler->getParameter('knp_pdf_timeout'));

        return $pdf;
    }
}
