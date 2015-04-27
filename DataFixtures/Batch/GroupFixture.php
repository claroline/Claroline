<?php

namespace HeVinci\CompetencyBundle\DataFixtures\Batch;

use Claroline\CoreBundle\Entity\Group;
use HeVinci\CompetencyBundle\Util\DataFixture;

class GroupFixture extends DataFixture
{
    public function load()
    {
        $this->flushSuites($this->loadGroupData(), 25, function ($group) {
            $this->buildGroup($group[0]);
        });
    }

    public function unload()
    {
        $groupNames = array_map(function ($group) {
            return $group[0];
        }, $this->loadGroupData());

        $this->createQueryBuilder()
            ->delete('Claroline\CoreBundle\Entity\Group', 'g')
            ->where('g.name IN (:names)')
            ->getQuery()
            ->setParameter(':names', $groupNames)
            ->execute();
    }

    private function loadGroupData()
    {
        return $this->loadCsvData(__DIR__ . '/../files/groups.csv', ',');
    }


    private function buildGroup($name)
    {
        $group = new Group();
        $group->setName($name);
        $this->om->persist($group);

        return $group;
    }
} 