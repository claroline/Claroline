<?php

namespace Innova\MediaResourceBundle\Manager;

use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Doctrine\ORM\EntityManager;
use Innova\MediaResourceBundle\Entity\HelpLink;
use Innova\MediaResourceBundle\Entity\HelpText;
use Innova\MediaResourceBundle\Entity\MediaResource;
use Innova\MediaResourceBundle\Entity\Region;
use Innova\MediaResourceBundle\Entity\RegionConfig;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("innova_media_resource.manager.media_resource_region")
 */
class RegionManager
{
    protected $em;
    protected $ut;

    /**
     * @DI\InjectParams({
     *      "em"                    = @DI\Inject("doctrine.orm.entity_manager"),
     *      "ut"                    = @DI\Inject("claroline.utilities.misc"),
     * })
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, ClaroUtilities $ut)
    {
        $this->em = $em;
        $this->ut = $ut;
    }

    public function save(Region $region)
    {
        $this->em->persist($region);
        $this->em->flush();

        return $region;
    }

    public function getRepository()
    {
        return $this->em->getRepository('InnovaMediaResourceBundle:Region');
    }

    public function copyRegion(MediaResource $mr, Region $region)
    {
        $entity = new Region();
        $entity->setMediaResource($mr);
        $regionConfig = new RegionConfig();
        $regionConfig->setRegion($entity);
        $entity->setRegionConfig($regionConfig);

        $entity->setStart($region->getStart());
        $entity->setEnd($region->getEnd());
        $entity->setNote($region->getNote());
        //create a new guid for the region (this will break the related region help)
        $entity->setUuid($this->ut->generateGuid());

        $oldRegionConfig = $region->getRegionConfig();
        $helpTexts = $oldRegionConfig->getHelpTexts();
        foreach ($helpTexts as $helpText) {
            $ht = new HelpText();
            $ht->setText($helpText->getText());
            $ht->setRegionConfig($regionConfig);
            $regionConfig->addHelpText($ht);
        }
        $helpLinks = $oldRegionConfig->getHelpLinks();
        foreach ($helpLinks as $helpLink) {
            $hl = new HelpLink();
            $hl->setUrl($helpLink->getUrl());
            $hl->setRegionConfig($regionConfig);
            $regionConfig->addHelpLink($hl);
        }
        $regionConfig->setLoop($oldRegionConfig->isLoop());
        $regionConfig->setRate($oldRegionConfig->isRate());
        $regionConfig->setBackward($oldRegionConfig->isBackward());
        $regionConfig->setHelpRegionUuid('');
        $this->save($entity);
    }

    private function checkData($data)
    {
        if (!isset($data['regions'])) {
            return false;
        }
        $toCheck = $data['regions'];
        $valid = true;
        foreach ($toCheck as $value) {
            $valid = isset($value['uuid'])
                && isset($value['start'])
                && isset($value['end'])
                && isset($value['note'])
                && isset($value['helps'])
                && isset($value['helps']['loop'])
                && isset($value['helps']['backward'])
                && isset($value['helps']['rate'])
                && isset($value['helps']['helpTexts'])
                && isset($value['helps']['helpLinks'])
                && isset($value['helps']['helpRegionUuid']);
            if (!$valid) {
                break;
            }
        }

        return $valid;
    }

    /**
     * Create/Update MediaResource regions and there config.
     *
     * @param MediaResource $mr
     * @param array of data
     */
    public function updateRegions(MediaResource $mr, $data)
    {
        if (!$this->checkData($data)) {
            return false;
        }
        $regions = $data['regions'];
        $this->deleteUnusedRegions($mr, $regions);
        // update or create région
        foreach ($regions as $region) {
            // update
            if (isset($region['id'])) {
                $entity = $this->getRepository()->find($region['id']);
            }
            // new
            else {
                $entity = new Region();
                $entity->setMediaResource($mr);
                $regionConfig = new RegionConfig();
                $regionConfig->setRegion($entity);
                $entity->setRegionConfig($regionConfig);
            }

            $entity->setStart($region['start']);
            $entity->setEnd($region['end']);
            $entity->setNote($region['note']);
            $entity->setUuid($region['uuid']);

            $config = $entity->getRegionConfig();
            $config->setLoop($region['helps']['loop']);
            $config->setRate($region['helps']['rate']);
            $config->setBackward($region['helps']['backward']);
            $config->setHelpRegionUuid($region['helps']['helpRegionUuid']);
            $helpTexts = $config->getHelpTexts();
            if (count($helpTexts) > 0) {
                $i = 0;
                foreach ($helpTexts as $helpText) {
                    $helpText->setText($region['helps']['helpTexts'][$i]['text']);
                    ++$i;
                }
            } else {
                $i = 0;
                foreach ($region['helps']['helpTexts'] as $helpText) {
                    $help = new HelpText();
                    $help->setText($region['helps']['helpTexts'][$i]['text']);
                    $help->setRegionConfig($config);
                    $config->addHelpText($help);
                    ++$i;
                }
            }
            $helpLinks = $config->getHelpLinks();
            if (count($helpLinks) > 0) {
                $i = 0;
                foreach ($helpLinks as $helpLink) {
                    $helpLink->setUrl($region['helps']['helpLinks'][$i]['url']);
                    ++$i;
                }
            } else {
                $i = 0;
                foreach ($region['helps']['helpLinks'] as $helpText) {
                    $help = new HelpLink();
                    $help->setUrl($region['helps']['helpLinks'][$i]['url']);
                    $help->setRegionConfig($config);
                    $config->addHelpLink($help);
                    ++$i;
                }
            }
            $this->save($entity);
        }

        return $mr;
    }

    /**
     * Delete unused regions.
     *
     * @param MediaResource $mr
     * @param array of regions to check
     */
    private function deleteUnusedRegions(MediaResource $mr, $toCheck)
    {
        // get existing regions in database
        $existing = $this->getRepository()->findBy(['mediaResource' => $mr]);
        // delete regions if they are no more here
        if (count($existing) > 0) {
            $toDelete = $this->checkIfRegionExists($existing, $toCheck);

            foreach ($toDelete as $unused) {
                $this->em->remove($unused);
            }
            $this->em->flush();
        }
    }

    private function checkIfRegionExists($existing, $toCheck)
    {
        $toDelete = [];
        foreach ($existing as $region) {
            $found = false;
            foreach ($toCheck as $current) {
                if (intval($current['id']) === $region->getId()) {
                    $found = true;
                    break;
                }
            }
            // if not found, this is an unused region
            if (!$found) {
                $toDelete[] = $region;
            }
        }

        return $toDelete;
    }
}
