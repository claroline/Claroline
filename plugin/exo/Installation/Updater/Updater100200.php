<?php

namespace UJM\ExoBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\InstallationBundle\Updater\Updater;

class Updater100200 extends Updater
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Removes unused mask decoder.
     */
    public function postUpdate()
    {
        /** @var ObjectManager $om */
        $om = $this->container->get('claroline.persistence.object_manager');

        /** @var MaskManager $maskManager */
        $maskManager = $this->container->get('claroline.manager.mask_manager');

        /** @var ResourceType $pathType */
        $quizType = $om
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findOneBy(['name' => 'ujm_exercise']);

        $this->log('Renaming mask decoder `VIEW_DOCIMOLOGY` to `view_docimology`...');
        $maskManager->renameMask($quizType, 'VIEW_DOCIMOLOGY', 'view_docimology');

        $this->log('Renaming mask decoder `MANAGE_PAPERS` to `manage_papers`...');
        $maskManager->renameMask($quizType, 'MANAGE_PAPERS', 'manage_papers');

        $om->flush();

        $this->log('Set exo numbering...');

        $sql = "UPDATE ujm_exercise SET numbering = 'none' WHERE numbering ='' OR numbering = NULL";
        $sth = $this->container->get('doctrine.dbal.default_connection')->prepare($sql);
        $sth->execute();

        $this->log('Clean old schema table...');
        $drop = 'DROP TABLE IF EXISTS ujm_link_hint_paper;';
        $dropSth = $this->container->get('doctrine.dbal.default_connection')->prepare($drop);
        $dropSth->execute();
    }
}
