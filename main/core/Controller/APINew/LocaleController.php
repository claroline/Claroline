<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew;

use Claroline\CoreBundle\Manager\LocaleManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Manages platform locales.
 *
 * @EXT\Route("/locale")
 */
class LocaleController
{
    /**
     * @var LocaleManager
     */
    private $manager;

    /**
     * LocaleController constructor.
     *
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.manager.locale_manager")
     * })
     *
     * @param LocaleManager $manager
     */
    public function __construct(LocaleManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * List platform locales.
     *
     * @EXT\Route("", name="apiv2_locale_list")
     * @EXT\Method("GET")
     *
     * @return JsonResponse
     */
    public function listAction()
    {
        return new JsonResponse(
            $this->manager->getLocales()
        );
    }
}
