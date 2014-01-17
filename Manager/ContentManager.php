<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\Service;
use Claroline\CoreBundle\Entity\Content;

/**
 * @Service("claroline.manager.content_manager")
 */
class ContentManager
{
    private $manager;
    private $content;

    /**
     * @InjectParams({
     *     "manager"        = @Inject("doctrine"),
     *     "persistence"    = @Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct($manager, $persistence)
    {
        $this->manager = $persistence;
        $this->content = $manager->getRepository('ClarolineCoreBundle:Content');
    }

    /**
     * Get Content
     *
     * Example: $contentManager->getContent(array('id' => $id, 'locale' => $locale));
     *
     * @param array $filter
     *
     * @return Content
     */
    public function getContent(array $filter)
    {
        return $this->content->findOneBy($filter);
    }

    /**
     * Create a new content.
     *
     * @param string $title
     * @param string $text
     * @param string $locale
     *
     * @return integer The id of the new content.
     */
    public function createContent($title, $text, $locale = null)
    {
        if ($title or $text) {
            $content = new Content();
            $content->setTitle($title);
            $content->setContent($text);

            if ($locale) {
                $content->setTranslatableLocale($locale);
            }

            $this->manager->persist($content);
            $this->manager->flush();

            return $content->getId();
        }
    }

    /**
     * Update a content.
     *
     * @param string $content
     * @param string $title
     * @param string $text
     * @param string $locale
     */
    public function updateContent($content, $title = null, $text = null, $locale = null)
    {
        if ($title) {
            $content->setTitle($title);
        }

        if ($text) {
            $content->setContent($text);
        }

        if ($locale) {
            $content->setTranslatableLocale($locale);
        }

        $this->manager->persist($content);
        $this->manager->flush();
    }


    /**
     * Delete a content
     *
     * @param Content $content
     */
    public function deleteContent(Content $content)
    {
        $this->manager->remove($content);
        $this->manager->flush();
    }
}
