<?php

namespace Claroline\CoreBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\MimeType;

/**
 * Mime types data fixture.
 */
class LoadMimeTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Loads a set of common mime types used within the platform.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $mimeTypeNames = array(
            'video/mp4',
            'video/mov',
            'video/flv',
            'audio/ogg',
            'application/zip',
            'image/png',
            'image/bmp',
            'image/jpg',
            'image/jpeg',
            'image/text',
            'claroline/default',
        );

        foreach ($mimeTypeNames as $mimeTypeName) {
            $mimeType = new MimeType();
            $mimeType->setName($mimeTypeName);
            $manager->persist($mimeType);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 4;
    }
}
