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

use Claroline\CoreBundle\Entity\Content;
use Claroline\CoreBundle\Entity\ContentTranslation;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;

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
    public function __construct(Registry $manager, EntityManager $entityManager, ObjectManager $persistence)
    {
        $this->manager = $persistence;
        $this->entityManager = $entityManager;
        $this->content = $manager->getRepository('ClarolineCoreBundle:Content');
        $this->translations = $manager->getRepository('ClarolineCoreBundle:ContentTranslation');
    }

    /**
     * Get Content
     *
     * Example: $contentManager->getContent(array('id' => $id));
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
     * Get translated Content
     *
     * Example: $contentManager->getTranslatedContent(array('id' => $id));
     *
     * @param array $filter
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
                ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, 'fr')
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
     * @param string $translatedContent array('en' => array('content' => 'foo', 'title' => 'foo'))
     * @param string $type A type of content
     *
     * @return integer The id of the new content.
     */
    public function createContent($translatedContent, $type = null)
    {
        $content = new Content();
        $content->setType($type);

        $this->updateContent($content, $translatedContent);

        return $content->getId();
    }

    /**
     * Update a content.
     *
     * @param string $translatedContent array('en' => array('content' => 'foo', 'title' => 'foo'))
     * @param string $content Content Entity
     */
    public function updateContent($content, $translatedContents)
    {
        foreach ($translatedContents as $lang => $translatedContent) {
            if (isset($translatedContent["title"]) and $translatedContent["title"] !== '' or
                isset($translatedContent["content"]) and $translatedContent["content"] !== '') {
                if (isset($translatedContent["title"])) {
                    $content->setTitle($translatedContent["title"]);
                }
                if (isset($translatedContent["content"])) {
                    $content->setContent($translatedContent["content"]);
                }

                $content->setTranslatableLocale($lang);
                $this->manager->persist($content);
                $this->manager->flush();
            }
        }
    }

    /**
     * Delete a content
     *
     * @param Content $content
     */
    public function deleteContent($content)
    {
        $this->manager->remove($content);
        $this->manager->flush();
    }

    /**
     * Delete a translation of content
     *
     * @param $locale
     * @param $id
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
