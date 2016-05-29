<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API\Admin;

use JMS\DiExtraBundle\Annotation as DI;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use Claroline\CoreBundle\Manager\LocaleManager;
use FOS\RestBundle\Controller\Annotations\Get;

/**
 * @NamePrefix("api_")
 */
class LocaleController extends FOSRestController
{
    /**
     * @DI\InjectParams({
     *    "localeManager" = @DI\Inject("claroline.manager.locale_manager")
     * })
     */
    public function __construct(LocaleManager $localeManager)
    {
        $this->localeManager = $localeManager;
    }

    /**
     * @Get("/locales/available", name="get_available_locales", options={ "method_prefix" = false })
     */
    public function getAvailableLocalesAction()
    {
        return $this->localeManager->getLocaleListForSelect();
    }
}
