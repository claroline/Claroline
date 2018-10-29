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

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\AbstractApiController;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Entity\Tab\HomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\Widget;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @EXT\Route("/")
 */
class HomeController extends AbstractApiController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var FinderProvider */
    private $finder;
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var TranslatorInterface */
    private $translator;

    /**
     * HomeController constructor.
     *
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "finder"        = @DI\Inject("claroline.api.finder"),
     *     "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer"    = @DI\Inject("claroline.api.serializer"),
     *     "translator"    = @DI\Inject("translator")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param FinderProvider                $finder
     * @param ObjectManager                 $om
     * @param SerializerProvider            $serializer
     * @param TranslatorInterface           $translator
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        FinderProvider $finder,
        ObjectManager $om,
        SerializerProvider $serializer,
        TranslatorInterface $translator
    ) {
        $this->authorization = $authorization;
        $this->finder = $finder;
        $this->om = $om;
        $this->serializer = $serializer;
        $this->translator = $translator;
    }

    /**
     * @EXT\Route(
     *     "/",
     *     name="apiv2_home"
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:home:new_home.html.twig")
     *
     * @return array
     *
     * @todo : move me. No template rendering in API controllers
     */
    public function newHomeAction()
    {
        $tabs = $this->finder->search(
            HomeTab::class,
            ['filters' => ['type' => HomeTab::TYPE_HOME]]
        );

        $tabs = array_filter($tabs['data'], function ($data) {
            return $data !== [];
        });
        $orderedTabs = [];

        foreach ($tabs as $tab) {
            $orderedTabs[$tab['position']] = $tab;
        }
        ksort($orderedTabs);

        if (0 === count($orderedTabs)) {
            $defaultTab = new HomeTab();
            $defaultTab->setType(HomeTab::TYPE_HOME);
            $this->om->persist($defaultTab);
            $defaultHomeTabConfig = new HomeTabConfig();
            $defaultHomeTabConfig->setHomeTab($defaultTab);
            $defaultHomeTabConfig->setName($this->translator->trans('home', [], 'platform'));
            $defaultHomeTabConfig->setLongTitle($this->translator->trans('home', [], 'platform'));
            $defaultHomeTabConfig->setLocked(true);
            $defaultHomeTabConfig->setTabOrder(0);
            $this->om->persist($defaultHomeTabConfig);
            $this->om->flush();
            $orderedTabs[] = $this->serializer->serialize($defaultTab);
        }

        return [
            'editable' => $this->authorization->isGranted('ROLE_ADMIN') ||
                $this->authorization->isGranted('ROLE_HOME_MANAGER'),
            'administration' => false,
            'context' => [
                'type' => Widget::CONTEXT_HOME,
            ],
            'tabs' => array_values($orderedTabs),
        ];
    }
}
