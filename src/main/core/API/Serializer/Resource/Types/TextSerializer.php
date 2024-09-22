<?php

namespace Claroline\CoreBundle\API\Serializer\Resource\Types;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Resource\Revision;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Template\PlaceholderManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TextSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly PlaceholderManager $placeholderManager
    ) {
    }

    public function getSchema(): string
    {
        return '#/main/core/text.json';
    }

    public function getName(): string
    {
        return 'text';
    }

    public function serialize(Text $text): array
    {
        return [
            'id' => $text->getUuid(),
            'raw' => $text->getContent(),
            'content' => $this->placeholderManager->replacePlaceholders($text->getContent() ?? ''),
            'meta' => [
                'version' => $text->getVersion(),
            ],
        ];
    }

    public function deserialize(array $data, Text $text, array $options = []): Text
    {
        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $text);
        } else {
            $text->refreshUuid();
        }

        $user = $this->tokenStorage->getToken()?->getUser();
        if (isset($data['raw'])) {
            $revision = new Revision();
            $revision->setContent($data['raw']);
            $revision->setUser($user instanceof User ? $user : null);
            $revision->setText($text);
            $version = $text->getVersion() + 1;
            $revision->setVersion($version);
            $text->setVersion($version);
        }

        return $text;
    }
}
