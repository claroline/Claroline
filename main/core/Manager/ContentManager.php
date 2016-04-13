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
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @Service("claroline.manager.content_manager")
 */
class ContentManager
{
    private $manager;
    private $request;
    private $content;

    /**
     * @InjectParams({
     *     "manager"        = @Inject("doctrine"),
     *     "requestStack"   = @Inject("request_stack"),
     *     "persistence"    = @Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        Registry $manager,
        RequestStack $requestStack,
        ObjectManager $persistence
    ) {
        $this->manager = $persistence;
        $this->request = $requestStack->getCurrentRequest();
        $this->content = $manager->getRepository('ClarolineCoreBundle:Content');
        $this->translations = $manager->getRepository('ClarolineCoreBundle:ContentTranslation');
    }

    /**
     * Get Content.
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
     * Get translated Content.
     *
     * Example: $contentManager->getTranslatedContent(array('id' => $id));
     *
     * @param array $filter
     *
     * @return array
     */
    public function getTranslatedContent(array $filter)
    {
        $content = $this->getContent($filter);

        if ($content instanceof Content) {
            return $this->translations->findTranslations($content);
        }
    }

    /**
     * Create a new content.
     *
     * @param string $translatedContent array('en' => array('content' => 'foo', 'title' => 'foo'))
     * @param string $type              A type of content
     *
     * @return int The id of the new content.
     */
    public function createContent(array $translatedContent, $type = null)
    {
        $content = new Content();
        $content->setType($type);
        $locale = $this->request->getSession()->get('_locale');
        $this->updateContent(
            $content,
            $this->setDefault(
                $this->setDefault($translatedContent, 'title', $locale),
                'content',
                $locale
            )
        );

        return $content->getId();
    }

    /**
     * Update a content.
     *
     * @param $translatedContent array('en' => array('content' => 'foo', 'title' => 'foo'))
     * @param $content Content Entity
     */
    public function updateContent(Content $content, array $translatedContents)
    {
        $content = $this->resetContent($content, $translatedContents); // Gedmo bug #321

        foreach ($translatedContents as $lang => $translatedContent) {
            $this->updateTranslation($content, $translatedContent, $lang);
        }
    }

    /**
     * Delete a translation of content.
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
            $this->manager->remove($content);
            $this->manager->flush();
        }
    }

    /**
     * Reset translated values of a content.
     *
     * @param $content A content entity
     * @param $translatedContent array('en' => array('content' => 'foo', 'title' => 'foo'))
     *
     * @return Claroline\CoreBundle\Entity\Content
     */
    private function resetContent(Content $content, array $translatedContents)
    {
        foreach ($translatedContents as $lang => $translatedContent) {
            $this->updateTranslation($content, $translatedContent, $lang, true);
        }

        $this->updateTranslation($content, $translatedContents['en']);

        return $content;
    }

    /**
     * Update a content translation.
     *
     * @param $content A content entity
     * @param translation array('content' => 'foo', 'title' => 'foo')
     * @param $locale A string with a locale value as 'en' or 'fr'
     * @param $reset A boolean in case of you whant to reset the values of the translation
     */
    private function updateTranslation(Content $content, $translation, $locale = 'en', $reset = false)
    {
        if (isset($translation['title'])) {
            $content->setTitle(($reset ? null : $translation['title']));
        }
        if (isset($translation['content'])) {
            $content->setContent(($reset ? null : $translation['content']));
        }

        $content->setTranslatableLocale($locale);
        $content->setModified();
        $this->manager->persist($content);
        $this->manager->flush();
    }

    /**
     * create_content in another language not longer create this content in the default language,
     * so this function is used for this purpose.
     *
     * @param $translatedContent array('en' => array('content' => 'foo', 'title' => 'foo'))
     * @param $field The name of a fiel as 'title' or 'content'
     * @param $locale A string with a locale value as 'en' or 'fr'
     *
     * @return array('en' => array('content' => 'foo', 'title' => 'foo'))
     */
    private function setDefault(array $translatedContent, $field, $locale)
    {
        if ($locale !== 'en') {
            if (isset($translatedContent['en'][$field]) and !strlen($translatedContent['en'][$field]) and
                isset($translatedContent[$locale][$field]) and strlen($translatedContent[$locale][$field])) {
                $translatedContent['en'][$field] = $translatedContent[$locale][$field];
            }
        }

        return $translatedContent;
    }
}
