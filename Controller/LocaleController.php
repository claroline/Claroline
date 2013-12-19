<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Manager\LocaleManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * @TODO doc
 */
class LocaleController
{
    private $localeManager;

    /**
     * @InjectParams({
     *     "localeManager"  = @Inject("claroline.common.locale_manager")
     * })
     */
    public function __construct(LocaleManager $localeManager)
    {
        $this->localeManager = $localeManager;
    }

    /**
     * Select a language
     *
     * @Route("/locale/select", name="claroline_locale_select")
     *
     * @Template("ClarolineCoreBundle:Locale:select.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function selectLangAction()
    {
        return array('locales' => $this->localeManager->getAvailableLocales());
    }

    /**
     * Change locale
     *
     * @Route("/locale/change/{_locale}", name="claroline_locale_change")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changeLocale($_locale)
    {
        $this->localeManager->setUserLocale($_locale);

        return new Response(200);
    }
}
