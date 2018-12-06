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

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Template\TemplateType;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.template.type")
 * @DI\Tag("claroline.serializer")
 */
class TemplateTypeSerializer
{
    use SerializerTrait;

    /**
     * @param TemplateType $templateType
     * @param array        $options
     *
     * @return array
     */
    public function serialize(TemplateType $templateType, array $options = [])
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
