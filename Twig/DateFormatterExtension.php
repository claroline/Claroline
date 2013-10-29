<?php

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
        } else {
            //symfony fallback
            $this->formatter = new \Symfony\Component\Intl\DateFormatter\IntlDateFormatter(
                $configHandler->getParameter('locale_language'),
                \Symfony\Component\Intl\DateFormatter\IntlDateFormatter::SHORT,
                \Symfony\Component\Intl\DateFormatter\IntlDateFormatter::SHORT,
                 date_default_timezone_get(),
                \Symfony\Component\Intl\DateFormatter\IntlDateFormatter::GREGORIAN
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
        return array('intl_format' => new \Twig_Filter_Method($this, 'intlDateFormat'));
    }

    /*
     * Format the date according to the locale.
     */
    public function intlDateFormat($date)
    {
        return $this->formatter->format($date);
    }

    /*
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
