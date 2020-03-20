<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\InstallationBundle\Updater\Updater;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater060500 extends Updater
{
    private $container;

    /** @var ObjectManager */
    private $om;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->om = $container->get('Claroline\AppBundle\Persistence\ObjectManager');
    }

    public function postUpdate()
    {
        $this->log('Updating the email validation parameter');
        $ch = $this->container->get('Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler');
        $emailConfirm = $ch->getParameter('registration_mail_validation');
        $ch->setParameter(
            'registration_mail_validation',
            true === $emailConfirm ? 2 : 1
        );

        $entities = $this->om->getRepository('ClarolineCoreBundle:User')->findAll();
        $totalObjects = count($entities);
        $i = 0;
        $this->log("Adding user email validation hash for {$totalObjects} users...");

        foreach ($entities as $entity) {
            if (!$entity->getEmailValidationHash()) {
                $entity->setEmailValidationHash(Uuid::uuid4()->toString());
                $this->om->persist($entity);
            }
            ++$i;

            if (0 === $i % 300) {
                $this->log("Flushing [{$i}/{$totalObjects}]");
                $this->om->flush();
            }
        }

        $this->om->flush();
        $this->log('Clearing object manager...');
        $this->om->clear();
        $this->log('done !');
    }
}
