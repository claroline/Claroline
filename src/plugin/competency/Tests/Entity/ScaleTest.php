<?php

namespace HeVinci\CompetencyBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use HeVinci\CompetencyBundle\Entity\Level;
use HeVinci\CompetencyBundle\Entity\Scale;
use PHPUnit\Framework\TestCase;

class ScaleTest extends TestCase
{
    public function testSetLevels()
    {
        $scale = new Scale();
        $scale->setLevels(new ArrayCollection([
           $this->makeLevel('A', 1),
           $this->makeLevel('B', 2),
        ]));
        $scale->setLevels(new ArrayCollection([
           $this->makeLevel('A', 1),
           $this->makeLevel('B', 2),
           $this->makeLevel('C', 3),
        ]));
        $this->assertEquals(3, $scale->getLevels()->count());
    }

    private function makeLevel($name, $value)
    {
        $level = new Level();
        $level->setName($name);
        $level->setValue($value);

        return $level;
    }
}
