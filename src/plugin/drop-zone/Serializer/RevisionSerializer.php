<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\DropZoneBundle\Entity\Document;
use Claroline\DropZoneBundle\Entity\Revision;
use Claroline\DropZoneBundle\Entity\RevisionComment;

class RevisionSerializer
{
    use SerializerTrait;

    /** @var SerializerProvider */
    private $serializer;

    private $revisionRepo;
    private $dropRepo;
    private $userRepo;

    public function __construct(ObjectManager $om, SerializerProvider $serializer)
    {
        $this->serializer = $serializer;

        $this->revisionRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Revision');
        $this->dropRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Drop');
        $this->userRepo = $om->getRepository('Claroline\CoreBundle\Entity\User');
    }

    public function getName()
    {
        return 'dropzone_revision';
    }

    /**
     * @return array
     */
    public function serialize(Revision $revision, array $options = [])
    {
        $serialized = [
            'id' => $revision->getUuid(),
            'creator' => $revision->getCreator() ?
                $this->serializer->serialize($revision->getCreator(), [Options::SERIALIZE_MINIMAL]) :
                null,
            'creationDate' => DateNormalizer::normalize($revision->getCreationDate()),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'documents' => array_values(array_map(function (Document $document) {
                    return $this->serializer->serialize($document);
                }, $revision->getDocuments()->toArray())),
                'comments' => array_values(array_map(function (RevisionComment $comment) use ($options) {
                    return $this->serializer->serialize($comment, $options);
                }, $revision->getComments()->toArray())),
            ]);
        }

        return $serialized;
    }

    /**
     * @param array    $data
     * @param Revision $revision
     */
    public function deserialize($data, Revision $revision = null): Revision
    {
        if (empty($revision)) {
            $revision = $this->revisionRepo->findOneBy(['uuid' => $data['id']]);
        }
        $revision = $revision ?: new Revision();

        $this->sipe('id', 'setUuid', $data, $revision);

        if (!$revision->getDrop() && isset($data['drop']['id'])) {
            $drop = $this->dropRepo->findOneBy(['uuid' => $data['drop']['id']]);
            $revision->setDrop($drop);
        }
        if (!$revision->getCreator() && isset($data['creator']['id'])) {
            $creator = isset($data['creator']['id']) ? $this->userRepo->findOneBy(['uuid' => $data['creator']['id']]) : null;
            $revision->setCreator($creator);
        }

        return $revision;
    }
}
