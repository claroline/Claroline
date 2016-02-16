<?php

namespace Innova\PathBundle\Controller\Widget;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;
use Innova\PathBundle\Manager\PathManager;
use Claroline\TagBundle\Manager\TagManager;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Innova\PathBundle\Entity\PathWidgetConfig;

/**
 * Class PathWidgetConfigController
 *
 * @Route(
 *      "/",
 *      name    = "innova_path_widget",
 *      service = "innova_path.controller.path_widget"
 * )
 */
class PathWidgetController
{
    /**
     * Current Entity Manager
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $om;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * Current path manager
     * @var \Innova\PathBundle\Manager\PathManager
     */
    protected $pathManager;

    /**
     * @var \Claroline\TagBundle\Manager\TagManager
     */
    protected $tagManager;

    /**
     * Class constructor
     *
     * @param \Doctrine\Common\Persistence\ObjectManager                                   $om
     * @param \Symfony\Component\Form\FormFactoryInterface                                 $formFactory
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @param \Innova\PathBundle\Manager\PathManager                                       $pathManager
     * @param \Claroline\TagBundle\Manager\TagManager                                      $tagManager
     */
    public function __construct(
        ObjectManager                 $om,
        FormFactoryInterface          $formFactory,
        AuthorizationCheckerInterface $authorizationChecker,
        PathManager                   $pathManager,
        TagManager                    $tagManager)
    {
        $this->om                   = $om;
        $this->formFactory          = $formFactory;
        $this->authorizationChecker = $authorizationChecker;
        $this->pathManager          = $pathManager;
        $this->tagManager           = $tagManager;
    }

    /**
     * Update or create the configuration of a Widget instance
     *
     * @param WidgetInstance $widgetInstance
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return array
     *
     * @Route(
     *      "/widget/config/{widgetInstance}",
     *      name= "innova_path_widget_config"
     * )
     * @Method("POST")
     * @Template("InnovaPathBundle:Widget:config.html.twig")
     */
    public function updateConfigAction(WidgetInstance $widgetInstance, Request $request)
    {
        // User can not edit the Widget
        if (!$this->authorizationChecker->isGranted('edit', $widgetInstance)) {
            throw new AccessDeniedException();
        }

        $config = $this->pathManager->getWidgetConfig($widgetInstance);
        if (null === $config) {
            $config = new PathWidgetConfig();
            $config->setWidgetInstance($widgetInstance);
        }

        $form = $this->formFactory->create('innova_path_widget_config', $config);

        $form->bind($request);
        if ($form->isValid()) {
            // Remove tags
            $tagsToRemove = $form->get('removeTags')->getData();
            if (!empty($tagsToRemove)) {
                // Search the Tag by ID
                $existingTags = $config->getTags()->toArray();
                $toRemoveArray = array_filter($existingTags, function($entry) use ($tagsToRemove) {
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
                $uniqueTags = array ();
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

        return array (
            'form'     => $form->createView(),
            'instance' => $widgetInstance,
            'tags'     => $this->tagManager->getPlatformTags(),
        );
    }
} 