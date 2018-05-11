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

use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
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
    protected $utilities;

    /**
     * @DI\InjectParams({ "utilities" = @DI\Inject("claroline.utilities.misc")})
     */
    public function __construct(ClaroUtilities $utilities)
    {
        $this->utilities = $utilities;
    }

    /**
     * Get filters of the service.
     *
     * @return \Twig_SimpleFilter
     */
    public function getFilters()
    {
        return ['intl_date_format' => new \Twig_SimpleFilter('intl_date_format', [$this, 'intlDateFormat'])];
    }

    /*
     * Format the date according to the locale.
     */
    public function intlDateFormat($date)
    {
        return $this->utilities->intlDateFormat($date);
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
