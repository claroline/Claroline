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
use Claroline\CoreBundle\Entity\ContentTranslation;
use Doctrine\ORM\Query;

/**
 * @Service("claroline.manager.content_manager")
 */
class ContentManager
{
    private $manager;
    private $entityManager;
    private $content;

    /**
     * @InjectParams({
     *     "manager"        = @Inject("doctrine"),
     *     "entityManager"  = @Inject("doctrine.orm.entity_manager"),
     *     "persistence"    = @Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct($manager, $entityManager, $persistence)
    {
        $this->manager = $persistence;
        $this->entityManager = $entityManager;
        $this->content = $manager->getRepository('ClarolineCoreBundle:Content');
        $this->translations = $manager->getRepository('ClarolineCoreBundle:ContentTranslation');
    }

    /**
     * Get Cont     ent
     *
     * Exameple: $contentManager->getContent(array('id' => $id));
     *
     * @return Claroline\CoreBundle\Entity\Content
     */
    public function getContent($filter)
    {
        return $this->content->findOneBy($filter);
    }

    /**
     * Get translated Content
     *
     * Exameple: $contentManager->getTranslatedContent(array('id' => $id));
     *
     * @return Array
     */
    public function getTranslatedContent($filter)
    {
        $content = $this->getContent($filter);

        if ($content instanceof Content) {

            $en = $this->entityManager->createQueryBuilder()
                ->select('content.content', 'content.title')
                ->from('ClarolineCoreBundle:Content', 'content')
                ->where('content.id = ' . $content->getId())
                ->getQuery()
                ->execute(
                    compact('entityId', 'entityClass'),
                    Query::HYDRATE_ARRAY
                );

            $translations = $this->translations->findTranslations($content);
            $translations['en'] = $en[0];

            return $translations;
        }
    }

    /**
     * Create a new content.
     *
     * @return The id of the new content.
     */
    public function createContent($title, $text, $locale = null, $type = null)
    {
        if ($title or $text) {
            $content = new Content();
            $content->setTitle($title);
            $content->setContent($text);

            if ($type) {
                $content->setType($type);
            }

            $this->manager->persist($content);
            $this->manager->flush();

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
     * @return This function doesn't return anything.
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
     * @return This function doesn't return anything.
     */
    public function deleteContent($content)
    {
        $this->manager->remove($content);
        $this->manager->flush();
    }

    /**
     * Delete a translation of content
     *
     * @return This function doesn't return anything.
     */
    public function deleteTranslation($locale, $id)
    {
        if ($locale === 'en') {
            $content = $this->content->findOneBy(array('id' => $id));
        } else {
            $content = $this->translations->findOneBy(array('foreignKey' => $id, 'locale' => $locale));
        }

        if ($content instanceof ContentTranslation or $content instanceof Content) {
            $this->deleteContent($content);
        }
    }
}
