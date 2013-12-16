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

use Claroline\CoreBundle\Library\Lang\LangService;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @TODO doc
 */
class LangController
{
    private $lang;
    private $session;

    /**
     * @InjectParams({
     *     "lang" = @Inject("claroline.common.lang_service"),
     *     "session" = @Inject("session")
     * })
     */
    public function __construct(LangService $lang, $session)
    {
        $this->lang = $lang;
        $this->session = $session;
    }

    /**
     * Select a language
     *
     * @Route("/lang/select", name="claroline_lang_select")
     *
     * @Template("ClarolineCoreBundle:Lang:select.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function selectLangAction()
    {
        return array('langs' => $this->lang->getLangs());
    }

    /**
     * Change locale
     *
     * @Route("/lang/change/{_locale}", name="claroline_lang_change")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changeLocale($_locale)
    {
        return new Response(200);
    }
}
