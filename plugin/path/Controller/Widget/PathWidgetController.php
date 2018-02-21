<?php

namespace Innova\PathBundle\Controller\Widget;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\TagBundle\Entity\Tag;
use Claroline\TagBundle\Manager\TagManager;
use Innova\PathBundle\Entity\PathWidgetConfig;
use Innova\PathBundle\Form\Type\PathWidgetConfigType;
use Innova\PathBundle\Manager\WidgetManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PathWidgetController
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorization;

    /**
     * @var WidgetManager
     */
    protected $widgetManager;

    /**
     * @var TagManager
     */
    protected $tagManager;

    /**
     * PathWidgetController constructor.
     *
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *     "formFactory"   = @DI\Inject("form.factory"),
     *     "widgetManager" = @DI\Inject("innova_path.manager.widget"),
     *     "tagManager"    = @DI\Inject("claroline.manager.tag_manager")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ObjectManager                 $om
     * @param FormFactoryInterface          $formFactory
     * @param WidgetManager                 $widgetManager
     * @param TagManager                    $tagManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager                 $om,
        FormFactoryInterface          $formFactory,
        WidgetManager                 $widgetManager,
        TagManager                    $tagManager)
    {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->formFactory = $formFactory;
        $this->widgetManager = $widgetManager;
        $this->tagManager = $tagManager;
    }

    /**
     * Update or create the configuration of a Widget instance.
     *
     * @EXT\Route("/widget/config/{widgetInstance}", name= "innova_path_widget_config")
     * @EXT\Method("POST")
     * @EXT\Template("InnovaPathBundle:Widget:config.html.twig")
     *
     * @param WidgetInstance $widgetInstance
     * @param Request        $request
     *
     * @throws AccessDeniedException
     *
     * @return array|Response
     */
    public function updateConfigAction(WidgetInstance $widgetInstance, Request $request)
    {
        // User can not edit the Widget
        if (!$this->authorization->isGranted('edit', $widgetInstance)) {
            throw new AccessDeniedException();
        }

        $config = $this->widgetManager->getConfig($widgetInstance);
        if (null === $config) {
            $config = new PathWidgetConfig();
            $config->setWidgetInstance($widgetInstance);
        }

        $form = $this->formFactory->create(new PathWidgetConfigType(), $config);

        $form->handleRequest($request);
        if ($form->isValid()) {
            // Remove tags
            $tagsToRemove = $form->get('removeTags')->getData();
            if (!empty($tagsToRemove)) {
                // Search the Tag by ID
                $existingTags = $config->getTags()->toArray();
                $toRemoveArray = array_filter($existingTags, function (Tag $entry) use ($tagsToRemove) {
                    return in_array($entry->getId(), $tagsToRemove);
                });

                foreach ($toRemoveArray as $toRemove) {
                    $config->removeTag($toRemove);
                }
            }

            // Add tags
            $tags = $form->get('tags')->getData();
            if (!empty($tags)) {
                // Ge the list of Tags from data String
                $tags = explode(',', $tags);
                $uniqueTags = [];
                foreach ($tags as $tag) {
                    $value = trim($tag);
                    if (!empty($value)) {
                        $uniqueTags[strtolower($value)] = $value;
                    }
                }

                foreach ($uniqueTags as $tagName) {
                    $tagObject = $this->tagManager->getOnePlatformTagByName($tagName);
                    if (!empty($tagObject)) {
                        $config->addTag($tagObject);
                    }
                }
            }

            $this->om->persist($config);
            $this->om->flush();

            return new Response('success', 204);
        }

        return [
            'form' => $form->createView(),
            'instance' => $widgetInstance,
            'tags' => $this->tagManager->getPlatformTags(),
        ];
    }
}
