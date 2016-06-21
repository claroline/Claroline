<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Doctrine\ORM\Query;

class ContentTranslationRepository extends TranslationRepository
{
    public function findTranslations($content)
    {
        $translations = parent::findTranslations($content);

        $translations['en'] = $this->findOriginalContent($content->getId());

        return $translations;
    }

    public function findOriginalContent($id)
    {
        $query = $this->_em->createQueryBuilder()
            ->select('content.content', 'content.title')
            ->from('ClarolineCoreBundle:Content', 'content')
            ->where('content.id = '.$id)
            ->getQuery()
            ->execute(
                compact('entityId', 'entityClass'),
                Query::HYDRATE_ARRAY
            );

        if (isset($query[0])) {
            return $query[0];
        }
    }
}
