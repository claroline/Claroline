<?php

namespace HeVinci\CompetencyBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use HeVinci\CompetencyBundle\Entity\Level;
use HeVinci\CompetencyBundle\Util\UnitTestCase;

class LevelTransformerTest extends UnitTestCase
{
    private $transformer;

    protected function setUp()
    {
        $this->transformer = new LevelTransformer(
            $this->mock('Doctrine\Common\Persistence\ObjectManager')
        );
    }

    /**
     * @dataProvider transformProvider
     *
     * @param $value
     * @param $result
     */
    public function testTransform($value, $result)
    {
        $this->assertEquals($result, $this->transformer->transform($value));
    }

    /**
     * @dataProvider reverseTransformProvider
     *
     * @param $value
     * @param array $expectedLevels
     */
    public function testReverseTransform($value, array $expectedLevels)
    {
        $result = $this->transformer->reverseTransform($value);
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $result);
        $this->assertEquals(count($expectedLevels), $result->count());

        foreach ($expectedLevels as $value => $name) {
            $this->assertEquals($name, $result[$value]->getName());
            $this->assertEquals($value, $result[$value]->getValue());
        }
    }

    public function transformProvider()
    {
        return [
            [null, ''],
            [new ArrayCollection(), ''],
            [
                new ArrayCollection([
                    $this->makeLevel('Foo'),
                    $this->makeLevel('Bar'),
                    $this->makeLevel('Baz')
                ]),
                "Foo\nBar\nBaz\n"
            ],
        ];
    }

    public function reverseTransformProvider()
    {
        return [
            ["Foo\nBar\nBaz", ['Foo', 'Bar', 'Baz']],
            ["  Foo\n\nBar\n   ", ['Foo', 'Bar']],
            ["\nFoo Bar\n   ", ['Foo Bar']]
        ];
    }

    private function makeLevel($name)
    {
        $level = new Level();
        $level->setName($name);

        return $level;
    }
}
