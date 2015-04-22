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

use Doctrine\ORM\Query;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;
use Gedmo\Translatable\Translatable;

/**
 * @Service("claroline.manager.translation_manager")
 */
class TranslationManager
{
    private $manager;
    private $translationRepository;

    /**
     * @InjectParams({
     *     "em" = @Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(
        $em
    )
    {
        $this->manager = $em;
    }

    /**
     * Get translations
     *
     * Example: $contentManager->getTranslatedContent();
     *
     * @param array $filter
     *
     * @return Content
     */
    public function getTranslatedContent($object)
    {
        if ($object instanceof Translatable) {
            $repo = $this->manager->getRepository($this->getTranslatableRepoClass($object));
            $translations = $repo->findTranslations($object);
            $translations['en'] = $this->findOriginalContent($object);
            return $translations;
        }

    }

    private function findOriginalContent($object)
    {
        $class = get_class($object);
        $field = $this->getTranslatableField($object);

        $result = $this->manager->createQueryBuilder()
            ->select('object.' . $field)
            ->from($class, 'object')
            ->where('object.id = ' . $object->getId())
            ->getQuery()
            ->execute(
                compact('entityId', 'entityClass'),
                Query::HYDRATE_ARRAY
            );

        if (isset($result[0])) {
            return $result[0];
        }
    }

    private function getTranslatableField($object)
    {
        $refClass = new \ReflectionClass($object);

        foreach ($refClass->getProperties() as $refProperty) {
            if (preg_match('/Translatable/', $refProperty->getDocComment(), $matches)) {
                return $refProperty->getName();
            }
        }
    }

    private function getTranslatableRepoClass($object)
    {
        $refClass = new \ReflectionClass($object);
        $doc = $refClass->getDocComment();
        preg_match_all('/TranslationEntity\(class="([^"]+)"/', $doc, $matches, PREG_SET_ORDER);

        return $matches[0][1];
    }
}
