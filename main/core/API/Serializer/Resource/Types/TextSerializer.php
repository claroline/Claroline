<?php

namespace Claroline\CoreBundle\API\Serializer\Resource\Types;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Manager\TextManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.serializer.resource_text")
 * @DI\Tag("claroline.serializer")
 */
class TextSerializer
{
    use SerializerTrait;

    /** @var TextManager */
    private $manager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * TextSerializer constructor.
     *
     * @DI\InjectParams({
     *     "manager"      = @DI\Inject("claroline.manager.text_manager"),
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     *
     * @param TextManager           $manager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TextManager $manager, TokenStorageInterface $tokenStorage)
    {
        $this->manager = $manager;
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

        // TODO : replace `createRevision`. It calls om flush and persist and this is not allowed in serializer
        $revision = $this->manager->createRevision($text, $data['content'], $user === 'anon.' ? null : $user);

        return $revision->getText();
    }
}
