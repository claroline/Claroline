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

class ContentTranslationRepository extends TranslationRepository
{
    public function findTranslations($content)
    {
        $translations = parent::findTranslations($content);

        return $translations;
    }
}
