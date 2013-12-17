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
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * @TODO doc
 */
class LangController
{
    private $lang;
    private $session;
    private $context;
    private $manager;

    /**
     * @InjectParams({
     *     "lang"       = @Inject("claroline.common.lang_service"),
     *     "session"    = @Inject("session"),
     *     "context"    = @Inject("security.context"),
     *     "manager"    = @Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(LangService $lang, Session $session, SecurityContext $context, ObjectManager $manager)
    {
        $this->lang = $lang;
        $this->session = $session;
        $this->context = $context;
        $this->manager = $manager;
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
        $langs = $this->lang->getLangs();
        $user = $this->context->getToken()->getUser();

        if (isset($langs[$_locale]) and is_object($user)) {
            $user->setLocale($_locale);
            $this->manager->persist($user);
            $this->manager->flush();
        }

        return new Response(200);
    }
}
