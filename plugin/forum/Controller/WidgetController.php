<?php

namespace Claroline\ForumBundle\Controller;

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\ForumBundle\Form\Widget\LastMessageWidgetConfigType;
use Claroline\ForumBundle\Manager\Manager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

class WidgetController extends Controller
{
    private $authorization;
    private $formFactory;
    private $forumManager;
    private $request;

    /**
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "formFactory"   = @DI\Inject("form.factory"),
     *     "forumManager"  = @DI\Inject("claroline.manager.forum_manager"),
     *     "requestStack"  = @DI\Inject("request_stack")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        FormFactoryInterface $formFactory,
        Manager $forumManager,
        RequestStack $requestStack
    ) {
        $this->authorization = $authorization;
        $this->formFactory = $formFactory;
        $this->forumManager = $forumManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @EXT\Route(
     *     "/widget/listmessages/{widgetInstance}/config",
     *     name="claroline_forum_last_message_widget_configure"
     * )
     * @EXT\Method("POST")
     */
    public function updateLastMessagesForumWidgetConfig(WidgetInstance $widgetInstance)
    {
        if (!$this->authorization->isGranted('edit', $widgetInstance)) {
            throw new AccessDeniedException();
        }
        $lastMessageWidgetConfig = $this->forumManager->getConfig($widgetInstance);
        $form = $this->formFactory->create(new LastMessageWidgetConfigType(), $lastMessageWidgetConfig);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->forumManager->persistLastMessageWidgetConfig($lastMessageWidgetConfig);

            return new Response('', 204);
        }

        return $this->render(
            'ClarolineForumBundle:Widget:lastMessageWidgetConfig.html.twig',
            array(
                'form' => $form->createView(),
                'widgetInstance' => $widgetInstance,
            )
        );
    }
}
