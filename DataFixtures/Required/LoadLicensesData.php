<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Required;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\License;

/**
 * Licenses data fixture.
 */
class LoadLicensesData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Loads three (dummy) license types.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $licenseNames = array('public domain', 'all rights reserved', 'other');

        foreach ($licenseNames as $licenseName) {
            $license = new License();
            $license->setName($licenseName);
            $manager->persist($license);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 3;
    }
}
