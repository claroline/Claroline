<?php

namespace Claroline\AnnouncementBundle\Serializer;

use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Template\TemplateSerializer;
use Claroline\CoreBundle\Entity\Template\Template;

class AnnouncementAggregateSerializer
{
    use SerializerTrait;

    private $om;
    private $templateSerializer;

    public function __construct(
        ObjectManager $om,
        TemplateSerializer $templateSerializer
    ) {
        $this->om = $om;
        $this->templateSerializer = $templateSerializer;
    }

    public function getName(): string
    {
        return 'announcement_aggregate';
    }

    public function getClass(): string
    {
        return AnnouncementAggregate::class;
    }

    public function serialize(AnnouncementAggregate $announcements, ?array $options = []): array
    {
        $serialized = [
            'id' => $announcements->getUuid(),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            if ($announcements->getTemplateEmail()) {
                $serialized['templateEmail'] = $this->templateSerializer->serialize($announcements->getTemplateEmail(), [Options::SERIALIZE_MINIMAL]);
            }
            if ($announcements->getTemplatePdf()) {
                $serialized['templatePdf'] = $this->templateSerializer->serialize($announcements->getTemplatePdf(), [Options::SERIALIZE_MINIMAL]);
            }
        }

        return $serialized;
    }

    public function deserialize(array $data, AnnouncementAggregate $aggregate, ?array $options = []): AnnouncementAggregate
    {
        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $aggregate);
        } else {
            $aggregate->refreshUuid();
        }

        if (array_key_exists('templateEmail', $data)) {
            $template = null;
            if (!empty($data['templateEmail']) && !empty($data['templateEmail']['id'])) {
                $template = $this->om->getRepository(Template::class)->findOneBy(['uuid' => $data['templateEmail']['id']]);
            }

            $aggregate->setTemplateEmail($template);
        }

        if (array_key_exists('templatePdf', $data)) {
            $template = null;
            if (!empty($data['templatePdf']) && !empty($data['templatePdf']['id'])) {
                $template = $this->om->getRepository(Template::class)->findOneBy(['uuid' => $data['templatePdf']['id']]);
            }

            $aggregate->setTemplatePdf($template);
        }

        return $aggregate;
    }
}
