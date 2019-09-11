<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Serializer\Template;

use Claroline\CoreBundle\Entity\Template\TemplateType;

class TemplateTypeSerializer
{
    /**
     * @param TemplateType $templateType
     *
     * @return array
     */
    public function serialize(TemplateType $templateType)
    {
        $serialized = [
            'id' => $templateType->getUuid(),
            'name' => $templateType->getName(),
            'placeholders' => $templateType->getPlaceholders(),
            'defaultTemplate' => $templateType->getDefaultTemplate(),
        ];

        return $serialized;
    }
}
