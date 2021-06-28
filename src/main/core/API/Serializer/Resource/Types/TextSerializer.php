<?php

namespace Claroline\CoreBundle\API\Serializer\Resource\Types;

use Claroline\CoreBundle\Entity\Resource\Revision;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Template\PlaceholderManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TextSerializer
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var PlaceholderManager */
    private $placeholderManager;

    /**
     * TextSerializer constructor.
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        PlaceholderManager $placeholderManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->placeholderManager = $placeholderManager;
    }

    public function getSchema()
    {
        return '#/main/core/text.json';
    }

    public function getName()
    {
        return 'text';
    }

    public function serialize(Text $text): array
    {
        return [
            'id' => $text->getId(),
            'raw' => $text->getContent(),
            'content' => $this->placeholderManager->replacePlaceholders($text->getContent() ?? ''),
            'meta' => [
                'version' => $text->getVersion(),
            ],
        ];
    }

    public function deserialize(array $data, Text $text): Text
    {
        $user = $this->tokenStorage->getToken()->getUser();

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
