<?php

namespace Claroline\CoreBundle\API\Serializer\Resource\Types;

use Claroline\CoreBundle\Entity\Resource\Revision;
use Claroline\CoreBundle\Entity\Resource\Text;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TextSerializer
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * TextSerializer constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getSchema()
    {
        return '#/main/core/text.json';
    }

    /**
     * Serializes a Text resource entity for the JSON api.
     *
     * @param Text $text
     *
     * @return array
     */
    public function serialize(Text $text)
    {
        return [
            'id' => $text->getId(),
            'content' => $text->getContent(),
            'meta' => [
                'version' => $text->getVersion(),
            ],
        ];
    }

    /**
     * @param array $data
     * @param Text  $text
     *
     * @return Text
     */
    public function deserialize($data, Text $text)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if (isset($data['content'])) {
            $revision = new Revision();
            $revision->setContent($data['content']);
            $revision->setUser('anon.' === $user ? null : $user);
            $revision->setText($text);
            $version = $text->getVersion() + 1;
            $revision->setVersion($version);
            $text->setVersion($version);
        }

        return $text;
    }
}
