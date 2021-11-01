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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Entity\Template\TemplateContent;
use Claroline\CoreBundle\Entity\Template\TemplateType;

class TemplateSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var TemplateTypeSerializer */
    private $typeSerializer;

    private $templateRepo;
    private $templateTypeRepo;

    public function __construct(
        ObjectManager $om,
        TemplateTypeSerializer $typeSerializer
    ) {
        $this->om = $om;
        $this->typeSerializer = $typeSerializer;

        $this->templateRepo = $om->getRepository(Template::class);
        $this->templateTypeRepo = $om->getRepository(TemplateType::class);
    }

    public function getName()
    {
        return 'template';
    }

    public function serialize(Template $template, array $options = []): array
    {
        $serialized = [
            'id' => $template->getUuid(),
            'name' => $template->getName(),
            'type' => $this->typeSerializer->serialize($template->getType()),
            'system' => $template->isSystem(),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options) && !in_array(Options::SERIALIZE_LIST, $options)) {
            $contents = [];
            foreach ($template->getTemplateContents() as $content) {
                $contents[$content->getLang()] = [
                    'title' => $content->getTitle(),
                    'content' => $content->getContent(),
                ];
            }

            $serialized['contents'] = $contents;
        }

        return $serialized;
    }

    public function deserialize(array $data, Template $template): Template
    {
        $this->sipe('id', 'setUuid', $data, $template);
        $this->sipe('name', 'setName', $data, $template);

        if (isset($data['type'])) {
            $templateType = isset($data['type']['id']) ?
                $this->templateTypeRepo->findOneBy(['uuid' => $data['type']['id']]) :
                null;

            if ($templateType) {
                $template->setType($templateType);
            }
        }

        if (isset($data['contents'])) {
            foreach ($data['contents'] as $locale => $localizedData) {
                $content = $template->getTemplateContent($locale);
                if (empty($content)) {
                    $content = new TemplateContent();
                    $content->setLang($locale);
                    $template->addTemplateContent($content);
                }

                $this->sipe('title', 'setTitle', $localizedData, $content);
                $this->sipe('content', 'setContent', $localizedData, $content);
            }
        }

        // TODO : should not be managed here
        if (isset($data['defineAsDefault']) && $template->getType()) {
            $templateType = $template->getType();
            $templateType->setDefaultTemplate($template->getName());
            $this->om->persist($templateType);
        }

        return $template;
    }
}
