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

use Symfony\Component\HttpKernel\KernelInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class DateFormatterExtension extends \Twig_Extension
{
    protected $configHandler;
    protected $kernel;
    protected $formater;

    /**
     * @DI\InjectParams({ "configHandler" = @DI\Inject("claroline.config.platform_config_handler" )})
     */
    public function __construct(KernelInterface $kernel, $configHandler)
    {
        $this->kernel = $kernel;
        $this->configHandler = $configHandler;

        if (extension_loaded('intl')) {
            $this->formatter = new \IntlDateFormatter(
                $configHandler->getParameter('locale_language'),
                \IntlDateFormatter::SHORT,
                \IntlDateFormatter::SHORT,
                date_default_timezone_get(),
                \IntlDateFormatter::GREGORIAN
            );
        }
    }

    /**
     * Get filters of the service
     *
     * @return \Twig_Filter_Method
     */
    public function getFilters()
    {
        return array('intl_date_format' => new \Twig_Filter_Method($this, 'intlDateFormat'));
    }

    /*
     * Format the date according to the locale.
     */
    public function intlDateFormat($date)
    {
        if (extension_loaded('intl') and $this->formatter instanceof \IntlDateFormatter) {
            return $this->formatter->format($date);
        } elseif ($date instanceof \DateTime) {
            return $date->format('d-m-Y');
        }

        return date('d-m-Y', $date);
    }

    /**
     * Get the name of the twig extention.
     *
     * @return \String
     */
    public function getName()
    {
        return 'date_formatter_extension';
    }
}
